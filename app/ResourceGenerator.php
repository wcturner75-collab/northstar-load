<?php

declare(strict_types=1);

namespace Northstar;

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

        $buildToken = bin2hex(random_bytes(32));
        $tempId = bin2hex(random_bytes(16));
        $tempRoot = Path::join($configApp['paths']['build_temp'], $tempId);
        $resourceRoot = Path::join($tempRoot, $resourceName);
        $templateSrc = $configApp['paths']['fivem_template'];

        if (!is_dir($templateSrc)) {
            throw new \RuntimeException('Master template missing.');
        }

        try {
            self::mkdirp($tempRoot);
            self::mkdirp($resourceRoot);
            self::copyTree($templateSrc, $resourceRoot);

            $mediaMap = self::copyMedia($validated, $userId, $resourceRoot, $configApp);
            $runtimeConfig = self::rewriteConfigMedia($validated, $mediaMap);
            $configJson = json_encode($runtimeConfig, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
            if ($configJson === false) {
                throw new \RuntimeException('Could not encode config.');
            }
            file_put_contents(Path::join($resourceRoot, 'config.json'), $configJson);

            $manifest = self::fxmanifest($resourceName, $configApp['builds']['runtime_version'] ?? '1.0.0');
            file_put_contents(Path::join($resourceRoot, 'fxmanifest.lua'), $manifest);

            self::assertResourceValid($resourceRoot);

            $zipName = $buildToken . '.zip';
            $zipRel = $zipName;
            $zipAbs = Path::join($configApp['paths']['builds'], $zipName);
            self::mkdirp($configApp['paths']['builds']);
            self::zipResource($tempRoot, $resourceName, $zipAbs);

            $size = filesize($zipAbs) ?: 0;
            $expireDays = (int) ($configApp['builds']['expire_days'] ?? 30);
            $runtime = (string) ($configApp['builds']['runtime_version'] ?? '1.0.0');

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
                $zipRel,
                $size,
                $runtime,
                $expireDays,
            ]);

            return [
                'buildToken' => $buildToken,
                'resourceName' => $resourceName,
                'fileSize' => $size,
                'downloadUrl' => '/download.php?build=' . $buildToken,
            ];
        } finally {
            self::rrmdir($tempRoot);
        }
    }

    /** @return array<int,string> mediaId => relative path inside resource */
    private static function copyMedia(array $config, int $userId, string $resourceRoot, array $configApp): array
    {
        $ids = [];
        foreach ($config['background']['mediaIds'] ?? [] as $id) {
            $ids[(int) $id] = 'backgrounds';
        }
        if (!empty($config['music']['mediaId'])) {
            $ids[(int) $config['music']['mediaId']] = 'music';
        }
        foreach ($config['components'] ?? [] as $comp) {
            if (!empty($comp['props']['mediaId'])) {
                $ids[(int) $comp['props']['mediaId']] = 'images';
            }
        }
        foreach ($config['content']['staff'] ?? [] as $staff) {
            if (!empty($staff['mediaId'])) {
                $ids[(int) $staff['mediaId']] = 'images';
            }
        }

        $map = [];
        foreach ($ids as $mediaId => $folder) {
            $media = MediaManager::findOwned($mediaId, $userId);
            if (!$media) {
                throw new \InvalidArgumentException('Missing media id ' . $mediaId);
            }
            $src = MediaManager::absolutePath($media, $configApp);
            if (!is_file($src)) {
                throw new \RuntimeException('Media file missing on disk.');
            }
            $destDir = Path::join($resourceRoot, 'web', 'assets', $folder);
            self::mkdirp($destDir);
            $safeName = preg_replace('/[^a-zA-Z0-9._-]/', '_', (string) $media['storage_name']) ?? 'asset.bin';
            $dest = Path::join($destDir, $safeName);
            if (!copy($src, $dest)) {
                throw new \RuntimeException('Failed to copy media.');
            }
            $map[$mediaId] = 'assets/' . $folder . '/' . $safeName;
        }
        return $map;
    }

    /** @param array<int,string> $mediaMap @return array<string,mixed> */
    private static function rewriteConfigMedia(array $config, array $mediaMap): array
    {
        $config['background']['assets'] = [];
        foreach ($config['background']['mediaIds'] ?? [] as $id) {
            if (isset($mediaMap[(int) $id])) {
                $config['background']['assets'][] = $mediaMap[(int) $id];
            }
        }
        unset($config['background']['mediaIds']);

        if (!empty($config['music']['mediaId']) && isset($mediaMap[(int) $config['music']['mediaId']])) {
            $config['music']['asset'] = $mediaMap[(int) $config['music']['mediaId']];
        } else {
            $config['music']['asset'] = null;
        }
        unset($config['music']['mediaId']);

        foreach ($config['components'] as &$comp) {
            if (!empty($comp['props']['mediaId']) && isset($mediaMap[(int) $comp['props']['mediaId']])) {
                $comp['props']['asset'] = $mediaMap[(int) $comp['props']['mediaId']];
            }
            unset($comp['props']['mediaId']);
        }
        unset($comp);

        foreach ($config['content']['staff'] as &$staff) {
            if (!empty($staff['mediaId']) && isset($mediaMap[(int) $staff['mediaId']])) {
                $staff['asset'] = $mediaMap[(int) $staff['mediaId']];
            }
            unset($staff['mediaId']);
        }
        unset($staff);

        return $config;
    }

    private static function fxmanifest(string $resourceName, string $version): string
    {
        $name = addslashes($resourceName);
        $ver = addslashes($version);
        return <<<LUA
fx_version 'cerulean'
game 'gta5'

name '{$name}'
author 'Northstar Load'
description 'Generated loading screen'
version '{$ver}'

loadscreen 'web/index.html'
loadscreen_cursor 'yes'

files {
    'web/index.html',
    'web/css/loadscreen.css',
    'web/js/runtime.js',
    'web/js/fivem.js',
    'config.json',
    'web/assets/**'
}

LUA;
    }

    private static function assertResourceValid(string $root): void
    {
        $required = [
            'fxmanifest.lua',
            'config.json',
            'web/index.html',
            'web/css/loadscreen.css',
            'web/js/runtime.js',
            'web/js/fivem.js',
        ];
        foreach ($required as $rel) {
            if (!is_file(Path::join($root, $rel))) {
                throw new \RuntimeException('Generated resource missing: ' . $rel);
            }
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

    private static function copyTree(string $src, string $dst): void
    {
        $iterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($src, \FilesystemIterator::SKIP_DOTS),
            \RecursiveIteratorIterator::SELF_FIRST
        );
        foreach ($iterator as $item) {
            /** @var \SplFileInfo $item */
            $rel = substr($item->getPathname(), strlen($src) + 1);
            $rel = str_replace('\\', '/', (string) $rel);
            if (Path::containsTraversal($rel)) {
                throw new \RuntimeException('Invalid template path.');
            }
            $target = Path::join($dst, $rel);
            if ($item->isDir()) {
                self::mkdirp($target);
            } else {
                self::mkdirp(dirname($target));
                if (!copy($item->getPathname(), $target)) {
                    throw new \RuntimeException('Template copy failed.');
                }
            }
        }
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
