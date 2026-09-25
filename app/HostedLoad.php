<?php

declare(strict_types=1);

namespace Northstar;

/**
 * Hosted loading screens served from load.northstarscripts.us (or app.url).
 * FiveM resources point loadscreen at /load?t={publish_token}.
 */
final class HostedLoad
{
    /** @param array<string,mixed> $configApp */
    public static function loadBaseUrl(array $configApp): string
    {
        $base = trim((string) ($configApp['hosting']['load_base_url'] ?? ''));
        if ($base === '') {
            $base = trim((string) ($configApp['app']['url'] ?? ''));
        }
        // Prefer the current request host when browsing locally so
        // localhost vs 127.0.0.1 doesn't break the hosted page.
        if (self::isLocalBase($base) && !empty($_SERVER['HTTP_HOST'])) {
            $https = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
                || ((int) ($_SERVER['SERVER_PORT'] ?? 80) === 443);
            $base = ($https ? 'https://' : 'http://') . $_SERVER['HTTP_HOST'];
        }
        return rtrim($base, '/');
    }

    private static function isLocalBase(string $base): bool
    {
        if ($base === '') {
            return true;
        }
        $host = parse_url($base, PHP_URL_HOST);
        return in_array($host, ['localhost', '127.0.0.1', '::1'], true);
    }

    /** Public URL FiveM will open for this project. */
    public static function publicUrl(string $publishToken, array $configApp): string
    {
        $token = self::normalizeToken($publishToken);
        return self::loadBaseUrl($configApp) . '/load?t=' . rawurlencode($token);
    }

    public static function normalizeToken(string $token): string
    {
        $token = trim($token);
        if ($token === '' || !preg_match('/^[a-f0-9]{32,64}$/i', $token)) {
            throw new \InvalidArgumentException('Invalid load token.');
        }
        return strtolower($token);
    }

    /** Ensure project has a stable publish token (created once, reused). */
    public static function ensurePublishToken(int $projectId, int $userId): string
    {
        $project = Project::findOwned($projectId, $userId);
        if (!$project) {
            throw new \RuntimeException('Project not found.');
        }
        $existing = (string) ($project['publish_token'] ?? '');
        if ($existing !== '' && preg_match('/^[a-f0-9]{32,64}$/i', $existing)) {
            return strtolower($existing);
        }
        $token = bin2hex(random_bytes(24)); // 48 hex chars
        $stmt = Database::pdo()->prepare(
            'UPDATE projects SET publish_token = ? WHERE id = ? AND user_id = ?'
        );
        $stmt->execute([$token, $projectId, $userId]);
        return $token;
    }

    /** @return array<string,mixed>|null */
    public static function findByToken(string $token): ?array
    {
        try {
            $token = self::normalizeToken($token);
        } catch (\InvalidArgumentException) {
            return null;
        }
        $stmt = Database::pdo()->prepare(
            "SELECT * FROM projects
             WHERE publish_token = ? AND status != 'archived'
             LIMIT 1"
        );
        $stmt->execute([$token]);
        $row = $stmt->fetch();
        if (!$row) {
            return null;
        }
        if (is_string($row['config_json'])) {
            $row['config'] = json_decode($row['config_json'], true) ?: [];
        } else {
            $row['config'] = is_array($row['config_json']) ? $row['config_json'] : [];
        }
        return $row;
    }

    /**
     * Runtime config with absolute media URLs scoped to this publish token.
     *
     * @param array<string,mixed> $project
     * @param array<string,mixed> $configApp
     * @return array<string,mixed>
     */
    public static function runtimeConfig(array $project, array $configApp): array
    {
        $userId = (int) $project['user_id'];
        $token = (string) $project['publish_token'];
        $raw = is_array($project['config'] ?? null) ? $project['config'] : [];
        $validated = BuilderConfigValidator::validate($raw, $userId, true);

        $base = self::loadBaseUrl($configApp);
        // Relative URLs so localhost vs 127.0.0.1 / CSP never break the page.
        // Absolute also included for FiveM CEF edge cases.
        $mediaUrl = static function (int $mediaId) use ($base, $token): string {
            $rel = '/api/load/media?t=' . rawurlencode($token) . '&id=' . $mediaId;
            return $base . $rel;
        };

        $cfg = $validated;
        $cfg['background']['assets'] = [];
        foreach ($cfg['background']['mediaIds'] ?? [] as $id) {
            $id = (int) $id;
            if ($id > 0 && MediaManager::findOwned($id, $userId)) {
                $cfg['background']['assets'][] = $mediaUrl($id);
            }
        }
        unset($cfg['background']['mediaIds']);

        if (($cfg['music']['source'] ?? 'file') === 'youtube') {
            $cfg['music']['asset'] = null;
            unset($cfg['music']['mediaId']);
        } elseif (!empty($cfg['music']['mediaId'])) {
            $mid = (int) $cfg['music']['mediaId'];
            $cfg['music']['asset'] = MediaManager::findOwned($mid, $userId) ? $mediaUrl($mid) : null;
            unset($cfg['music']['mediaId']);
        } else {
            $cfg['music']['asset'] = null;
            unset($cfg['music']['mediaId']);
        }

        foreach ($cfg['components'] as &$comp) {
            if (!empty($comp['props']['mediaId'])) {
                $mid = (int) $comp['props']['mediaId'];
                if (MediaManager::findOwned($mid, $userId)) {
                    $comp['props']['asset'] = $mediaUrl($mid);
                }
            }
            unset($comp['props']['mediaId']);
        }
        unset($comp);

        foreach ($cfg['content']['staff'] as &$staff) {
            if (!empty($staff['mediaId'])) {
                $mid = (int) $staff['mediaId'];
                if (MediaManager::findOwned($mid, $userId)) {
                    $staff['asset'] = $mediaUrl($mid);
                }
            }
            unset($staff['mediaId']);
        }
        unset($staff);

        $cfg['meta'] = $cfg['meta'] ?? [];
        $cfg['meta']['hosted'] = true;
        $cfg['meta']['publishToken'] = $token;

        return $cfg;
    }

    /**
     * Media IDs referenced by a project's builder config (for access checks).
     *
     * @param array<string,mixed> $config
     * @return list<int>
     */
    public static function referencedMediaIds(array $config): array
    {
        $ids = [];
        foreach ($config['background']['mediaIds'] ?? [] as $id) {
            $ids[] = (int) $id;
        }
        if (!empty($config['music']['mediaId'])) {
            $ids[] = (int) $config['music']['mediaId'];
        }
        foreach ($config['components'] ?? [] as $comp) {
            if (!empty($comp['props']['mediaId'])) {
                $ids[] = (int) $comp['props']['mediaId'];
            }
        }
        foreach ($config['content']['staff'] ?? [] as $staff) {
            if (!empty($staff['mediaId'])) {
                $ids[] = (int) $staff['mediaId'];
            }
        }
        $ids = array_values(array_unique(array_filter($ids, static fn ($n) => $n > 0)));
        return $ids;
    }

    public static function tokenFromRequest(): string
    {
        $t = (string) ($_GET['t'] ?? '');
        if ($t === '' && isset($_GET[''])) {
            // Support load?=TOKEN style
            $t = (string) $_GET[''];
        }
        if ($t === '' && !empty($_SERVER['QUERY_STRING'])) {
            // Bare ?TOKEN
            $qs = (string) $_SERVER['QUERY_STRING'];
            if (preg_match('/^[a-f0-9]{32,64}$/i', $qs)) {
                $t = $qs;
            }
        }
        return self::normalizeToken($t);
    }
}
