<?php

declare(strict_types=1);

namespace Northstar;

final class Security
{
    public static function sendHeaders(): void
    {
        if (headers_sent()) {
            return;
        }
        header('X-Content-Type-Options: nosniff');
        header('Referrer-Policy: strict-origin-when-cross-origin');
        // Never let Cloudflare/proxy cache HTML that embeds a CSRF token.
        header('Cache-Control: private, no-store, no-cache, must-revalidate, max-age=0');
        header('Pragma: no-cache');

        $path = (string) (parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/');
        $hosted = $path === '/load' || $path === '/load.php'
            || str_starts_with($path, '/api/load/')
            || str_starts_with($path, '/hosted/');

        if ($hosted) {
            // Public FiveM loadscreen + YouTube music embed
            header('Referrer-Policy: no-referrer');
            header("Content-Security-Policy: default-src 'self' https: data: blob:; script-src 'self' 'unsafe-inline' https://www.youtube.com https://www.youtube-nocookie.com https://www.google.com; frame-src https://www.youtube.com https://www.youtube-nocookie.com; media-src 'self' https: blob: data:; img-src 'self' https: data: blob:; style-src 'self' 'unsafe-inline' https://fonts.googleapis.com; font-src 'self' https://fonts.gstatic.com data:; connect-src 'self' https:");
            return;
        }

        header('X-Frame-Options: SAMEORIGIN');
        // AdSense + fonts; unsafe-inline kept as fallback for page boot straps
        header(
            "Content-Security-Policy: default-src 'self'; "
            . "img-src 'self' data: blob: https:; "
            . "media-src 'self' blob:; "
            . "style-src 'self' 'unsafe-inline' https://fonts.googleapis.com; "
            . "font-src 'self' https://fonts.gstatic.com data:; "
            . "script-src 'self' 'unsafe-inline' https://pagead2.googlesyndication.com https://www.googletagservices.com https://www.google.com https://partner.googleadservices.com https://tpc.googlesyndication.com; "
            . "connect-src 'self' https://pagead2.googlesyndication.com https://googleads.g.doubleclick.net https://www.google.com https://tpc.googlesyndication.com; "
            . "frame-src https://googleads.g.doubleclick.net https://tpc.googlesyndication.com https://www.google.com https://pagead2.googlesyndication.com https://www.youtube.com https://www.youtube-nocookie.com; "
            . "frame-ancestors 'self'"
        );
    }

    public static function ensureCsrfToken(bool $force = false): string
    {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            return '';
        }
        if ($force || empty($_SESSION['csrf_token']) || !is_string($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['csrf_token'];
    }

    public static function csrfToken(): string
    {
        return self::ensureCsrfToken();
    }

    public static function verifyCsrf(?string $token): bool
    {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            return false;
        }
        $session = $_SESSION['csrf_token'] ?? '';
        if (!is_string($token) || $token === '' || !is_string($session) || $session === '') {
            return false;
        }
        return hash_equals($session, $token);
    }

    public static function requireCsrfFromRequest(): void
    {
        $token = $_SERVER['HTTP_X_CSRF_TOKEN'] ?? ($_POST['_csrf'] ?? null);
        if (!self::verifyCsrf(is_string($token) ? $token : null)) {
            Response::jsonError('Invalid CSRF token. Refresh the page and try again.', 403, 'csrf');
        }
    }

    public static function e(?string $value): string
    {
        return htmlspecialchars((string) $value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    }

    public static function hashIp(?string $ip): string
    {
        return hash('sha256', (string) $ip);
    }

    public static function clientIp(): string
    {
        return $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
    }

    public static function userAgent(): string
    {
        return substr($_SERVER['HTTP_USER_AGENT'] ?? '', 0, 512);
    }
}
