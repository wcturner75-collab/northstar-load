<?php

declare(strict_types=1);

namespace Northstar;

/**
 * Schema / connection health. Broken DB → /system-status (not a white screen).
 */
final class DbHealth
{
    /** @var list<string> */
    private const REQUIRED_TABLES = [
        'users',
        'user_sessions',
        'login_attempts',
        'entitlements',
        'templates',
        'projects',
        'project_versions',
        'media',
        'builds',
    ];

    /** Paths that must stay reachable when the schema is broken. */
    public static function isExemptPath(?string $path = null): bool
    {
        $path = $path ?? (string) (parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/');
        $path = rtrim($path, '/') ?: '/';
        $exempt = [
            '/system-status',
            '/system-status.php',
            '/assets',
            '/hosted',
        ];
        foreach ($exempt as $prefix) {
            if ($path === $prefix || str_starts_with($path, $prefix . '/')) {
                return true;
            }
        }
        // static files under assets already short-circuit via router usually
        if (preg_match('#\.(css|js|png|jpg|jpeg|webp|gif|ico|map|woff2?)$#i', $path)) {
            return true;
        }
        return false;
    }

    /**
     * @param array<string,mixed> $config
     * @return array{ok:bool,connected:bool,missing_tables:list<string>,missing_columns:list<string>,error:?string}
     */
    public static function check(array $config): array
    {
        $result = [
            'ok' => false,
            'connected' => false,
            'missing_tables' => [],
            'missing_columns' => [],
            'error' => null,
        ];

        try {
            if (!isset($GLOBALS['ns_db_ready']) || !$GLOBALS['ns_db_ready']) {
                // Attempt soft connect without throwing through bootstrap
                self::softConnect($config);
            }
            $pdo = Database::pdo();
            $result['connected'] = true;

            $tables = $pdo->query('SHOW TABLES')->fetchAll(\PDO::FETCH_COLUMN);
            $tables = array_map('strval', $tables ?: []);
            foreach (self::REQUIRED_TABLES as $need) {
                if (!in_array($need, $tables, true)) {
                    $result['missing_tables'][] = $need;
                }
            }

            if (in_array('users', $tables, true)) {
                $cols = $pdo->query('SHOW COLUMNS FROM users')->fetchAll(\PDO::FETCH_COLUMN);
                $cols = array_map('strval', $cols ?: []);
                foreach (['editor_mode', 'role'] as $col) {
                    if (!in_array($col, $cols, true)) {
                        $result['missing_columns'][] = 'users.' . $col;
                    }
                }
            }
            if (in_array('projects', $tables, true)) {
                $cols = $pdo->query('SHOW COLUMNS FROM projects')->fetchAll(\PDO::FETCH_COLUMN);
                $cols = array_map('strval', $cols ?: []);
                if (!in_array('publish_token', $cols, true)) {
                    $result['missing_columns'][] = 'projects.publish_token';
                }
            }

            $result['ok'] = $result['missing_tables'] === [] && $result['missing_columns'] === [];
        } catch (\Throwable $e) {
            $result['error'] = $e->getMessage();
            $result['connected'] = false;
            $result['ok'] = false;
        }

        return $result;
    }

    /** @param array<string,mixed> $config */
    private static function softConnect(array $config): void
    {
        try {
            Database::init($config);
            $GLOBALS['ns_db_ready'] = true;
        } catch (\Throwable $e) {
            $GLOBALS['ns_db_ready'] = false;
            throw $e;
        }
    }

    /** @param array<string,mixed> $config */
    public static function guard(array $config): void
    {
        if (self::isExemptPath()) {
            return;
        }
        $status = self::check($config);
        if ($status['ok']) {
            return;
        }
        $qs = http_build_query([
            'reason' => !$status['connected'] ? 'connection' : 'schema',
        ]);
        header('Location: /system-status?' . $qs);
        exit;
    }
}
