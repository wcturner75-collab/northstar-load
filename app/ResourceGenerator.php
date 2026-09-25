<?php

declare(strict_types=1);

namespace Northstar;

/**
 * Packages a thin FiveM resource that points loadscreen at the hosted URL.
 * Media + runtime stay on Northstar (load.northstarscripts.us).
 */
final class ResourceGenerator
{
    /**
     * @param array<string,mixed> $configApp
     * @return array<string,mixed>
     */
    public static function generate(int $projectId, int $userId, array $configApp): array
    {
        Entitlement::assertCanBuild($userId, $configApp);

        $project = Project::findOwned($projectId, $userId);
        if (!$project) {
            throw new \RuntimeException('Project not found.');
        }

        $resourceName = ResourceName::assert((string) $project['resource_name']);
        $rawConfig = is_array($project['config'] ?? null) ? $project['config'] : [];
        $validated = BuilderConfigValidator::validate($rawConfig, $userId, true);
        Entitlement::assertConfigAllowed($validated, $userId, $configApp);

        $publishToken = HostedLoad::ensurePublishToken($projectId, $userId);
        $loadUrl = HostedLoad::publicUrl($publishToken, $configApp);

        $buildToken = bin2hex(random_bytes(32));
        $tempId = bin2hex(random_bytes(16));
        $tempRoot = Path::join($configApp['paths']['build_temp'], $tempId);
        $resourceRoot = Path::join($tempRoot, $resourceName);
        $runtime = (string) ($configApp['builds']['runtime_version'] ?? '1.2.0');

        try {
            self::mkdirp($tempRoot);
            self::mkdirp($resourceRoot);

            file_put_contents(
                Path::join($resourceRoot, 'fxmanifest.lua'),
                self::fxmanifest($resourceName, $runtime, $loadUrl)
            );
            file_put_contents(
                Path::join($resourceRoot, 'client.lua'),
                self::clientLua()
            );
            file_put_contents(
                Path::join($resourceRoot, 'README.txt'),
                self::readme($resourceName, $loadUrl)
            );

            self::assertResourceValid($resourceRoot);

            $zipName = $buildToken . '.zip';
            $zipAbs = Path::join($configApp['paths']['builds'], $zipName);
            self::mkdirp($configApp['paths']['builds']);
            self::zipResource($tempRoot, $resourceName, $zipAbs);

            $size = filesize($zipAbs) ?: 0;
            $expireDays = (int) ($configApp['builds']['expire_days'] ?? 30);

            $stmt = Database::pdo()->prepare(
                'INSERT INTO builds
                 (user_id, project_id, build_token, resource_name, file_path, file_size, runtime_version, status, expires_at)
                 VALUES (?, ?, ?, ?, ?, ?, ?, \'ready\', DATE_ADD(NOW(3), INTERVAL ? DAY))'
            );
            $stmt->execute([
                $userId,
                $projectId,
                $buildToken,
                $resourceName,
                $zipName,
                $size,
                $runtime,
                $expireDays,
            ]);

            return [
                'buildToken' => $buildToken,
                'resourceName' => $resourceName,
                'fileSize' => $size,
                'downloadUrl' => '/download.php?build=' . $buildToken,
                'loadUrl' => $loadUrl,
                'publishToken' => $publishToken,
                'hosted' => true,
            ];
        } finally {
            self::rrmdir($tempRoot);
        }
    }

    private static function fxmanifest(string $resourceName, string $version, string $loadUrl): string
    {
        $name = addslashes($resourceName);
        $ver = addslashes($version);
        // Escape for Lua single-quoted string
        $url = str_replace(["\\", "'"], ["\\\\", "\\'"], $loadUrl);

        return <<<LUA
fx_version 'cerulean'
game 'gta5'

name '{$name}'
author 'Northstar Load'
description 'Hosted loading screen — Northstar Load'
version '{$ver}'

-- Lives on Northstar hosting (edit in the web builder; no local HTML needed)
loadscreen '{$url}'
loadscreen_cursor 'yes'
loadscreen_manual_shutdown 'yes'

client_script 'client.lua'

LUA;
    }

    private static function clientLua(): string
    {
        return <<<'LUA'
-- Northstar Load — shut down hosted loadscreen when the session is ready
CreateThread(function()
    while not NetworkIsSessionStarted() do
        Wait(100)
    end
    ShutdownLoadingScreen()
    ShutdownLoadingScreenNui()
end)

LUA;
    }

    private static function readme(string $resourceName, string $loadUrl): string
    {
        return "NORTHSTAR LOAD — HOSTED RESOURCE\r\n"
            . "================================\r\n\r\n"
            . "Resource: {$resourceName}\r\n"
            . "Load URL: {$loadUrl}\r\n\r\n"
            . "1. Drop this folder into your server resources/\r\n"
            . "2. Add: ensure {$resourceName}\r\n"
            . "3. Restart the server (or start the resource)\r\n\r\n"
            . "The loading screen is hosted by Northstar Load.\r\n"
            . "Edit it anytime in the web builder — players see updates\r\n"
            . "without regenerating this ZIP (same publish link).\r\n\r\n"
            . "Do not change the loadscreen URL in fxmanifest.lua.\r\n";
    }

    private static function assertResourceValid(string $root): void
    {
        foreach (['fxmanifest.lua', 'client.lua', 'README.txt'] as $rel) {
            if (!is_file(Path::join($root, $rel))) {
                throw new \RuntimeException('Generated resource missing: ' . $rel);
            }
        }
        $manifest = (string) file_get_contents(Path::join($root, 'fxmanifest.lua'));
        if (!str_contains($manifest, 'loadscreen ') || (!str_contains($manifest, 'https://') && !str_contains($manifest, 'http://'))) {
            throw new \RuntimeException('Hosted loadscreen URL missing from manifest.');
        }
    }

    private static function zipResource(string $tempRoot, string $resourceName, string $zipAbs): void
    {
        $zip = new \ZipArchive();
        if ($zip->open($zipAbs, \ZipArchive::CREATE | \ZipArchive::OVERWRITE) !== true) {
            throw new \RuntimeException('Could not create ZIP.');
        }

        $base = Path::join($tempRoot, $resourceName);
        $iterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($base, \FilesystemIterator::SKIP_DOTS)
        );

        foreach ($iterator as $file) {
            /** @var \SplFileInfo $file */
            if (!$file->isFile()) {
                continue;
            }
            $full = $file->getPathname();
            $rel = substr($full, strlen($base) + 1);
            $rel = str_replace('\\', '/', $rel);
            if (Path::containsTraversal($rel)) {
                $zip->close();
                @unlink($zipAbs);
                throw new \RuntimeException('ZIP slip blocked.');
            }
            $entry = $resourceName . '/' . $rel;
            if (Path::containsTraversal($entry) || str_starts_with($entry, '/')) {
                $zip->close();
                @unlink($zipAbs);
                throw new \RuntimeException('ZIP slip blocked.');
            }
            $zip->addFile($full, $entry);
        }
        $zip->close();
    }

    private static function mkdirp(string $dir): void
    {
        if (!is_dir($dir) && !mkdir($dir, 0750, true) && !is_dir($dir)) {
            throw new \RuntimeException('mkdir failed: ' . $dir);
        }
    }

    private static function rrmdir(string $dir): void
    {
        if (!is_dir($dir)) {
            return;
        }
        $items = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($dir, \FilesystemIterator::SKIP_DOTS),
            \RecursiveIteratorIterator::CHILD_FIRST
        );
        foreach ($items as $item) {
            /** @var \SplFileInfo $item */
            if ($item->isDir()) {
                @rmdir($item->getPathname());
            } else {
                @unlink($item->getPathname());
            }
        }
        @rmdir($dir);
    }
}
