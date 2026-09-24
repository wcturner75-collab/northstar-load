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

        $s = $config['session'];
        session_name($s['name'] ?? 'NSLOADSESSID');

        session_set_cookie_params([
            'lifetime' => (int) ($s['lifetime'] ?? 7200),
            'path' => '/',
            'secure' => (bool) ($s['secure'] ?? false),
            'httponly' => (bool) ($s['httponly'] ?? true),
            'samesite' => $s['samesite'] ?? 'Lax',
        ]);

        ini_set('session.use_strict_mode', '1');
        ini_set('session.use_only_cookies', '1');
        session_start();

        if (empty($_SESSION['_ns_init'])) {
            $_SESSION['_ns_init'] = true;
            Security::ensureCsrfToken();
        }
    }

    public static function regenerate(): void
    {
        session_regenerate_id(true);
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
        if (ini_get('session.use_cookies')) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', [
                'expires' => time() - 42000,
                'path' => $params['path'],
                'domain' => $params['domain'] ?? '',
                'secure' => (bool) $params['secure'],
                'httponly' => (bool) $params['httponly'],
                'samesite' => $params['samesite'] ?? 'Lax',
            ]);
        }
        session_destroy();
    }
}
