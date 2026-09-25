<?php

declare(strict_types=1);

namespace Northstar;

final class Session
{
    /** @param array<string,mixed> $config */
    public static function start(array $config): void
    {
        if (session_status() === PHP_SESSION_ACTIVE) {
            return;
        }

        $s = $config['session'] ?? [];
        session_name((string) ($s['name'] ?? 'NSLOADSESSID'));

        $savePath = (string) ($config['paths']['sessions'] ?? '');
        if ($savePath === '' && defined('NORTHSTAR_ROOT')) {
            $savePath = NORTHSTAR_ROOT . '/storage/sessions';
        }
        if ($savePath !== '') {
            if (!is_dir($savePath)) {
                @mkdir($savePath, 0775, true);
            }
            if (is_dir($savePath) && is_writable($savePath)) {
                session_save_path($savePath);
            }
        }

        $secure = self::cookieSecureFlag($s['secure'] ?? 'auto');

        session_set_cookie_params([
            'lifetime' => (int) ($s['lifetime'] ?? 7200),
            'path' => '/',
            'domain' => (string) ($s['domain'] ?? ''),
            'secure' => $secure,
            'httponly' => (bool) ($s['httponly'] ?? true),
            'samesite' => (string) ($s['samesite'] ?? 'Lax'),
        ]);

        ini_set('session.use_strict_mode', '1');
        ini_set('session.use_only_cookies', '1');
        ini_set('session.cookie_httponly', '1');
        ini_set('session.cookie_samesite', (string) ($s['samesite'] ?? 'Lax'));
        if ($secure) {
            ini_set('session.cookie_secure', '1');
        }

        session_start();

        if (empty($_SESSION['_ns_init'])) {
            $_SESSION['_ns_init'] = true;
            Security::ensureCsrfToken(true);
        } else {
            Security::ensureCsrfToken(false);
        }
    }

    /**
     * Detect HTTPS correctly behind Cloudflare / LiteSpeed proxies.
     */
    public static function isHttps(): bool
    {
        if (!empty($_SERVER['HTTPS']) && strtolower((string) $_SERVER['HTTPS']) !== 'off') {
            return true;
        }
        if ((int) ($_SERVER['SERVER_PORT'] ?? 0) === 443) {
            return true;
        }
        $fwd = strtolower((string) ($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? ''));
        if ($fwd === 'https') {
            return true;
        }
        $cf = (string) ($_SERVER['HTTP_CF_VISITOR'] ?? '');
        if ($cf !== '' && str_contains($cf, '"scheme":"https"')) {
            return true;
        }
        return false;
    }

    /** @param mixed $setting true|false|'auto' */
    private static function cookieSecureFlag(mixed $setting): bool
    {
        if ($setting === true || $setting === 1 || $setting === '1') {
            return true;
        }
        if ($setting === false || $setting === 0 || $setting === '0') {
            return false;
        }
        // auto
        return self::isHttps();
    }

    public static function regenerate(): void
    {
        if (session_status() === PHP_SESSION_ACTIVE) {
            session_regenerate_id(true);
        }
        Security::ensureCsrfToken(true);
    }

    public static function get(string $key, mixed $default = null): mixed
    {
        return $_SESSION[$key] ?? $default;
    }

    public static function set(string $key, mixed $value): void
    {
        $_SESSION[$key] = $value;
    }

    public static function forget(string $key): void
    {
        unset($_SESSION[$key]);
    }

    public static function destroy(): void
    {
        $_SESSION = [];
        if (session_status() === PHP_SESSION_ACTIVE && ini_get('session.use_cookies')) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', [
                'expires' => time() - 42000,
                'path' => $params['path'] ?: '/',
                'domain' => $params['domain'] ?? '',
                'secure' => (bool) $params['secure'],
                'httponly' => (bool) $params['httponly'],
                'samesite' => $params['samesite'] ?? 'Lax',
            ]);
            session_destroy();
        }
    }
}
