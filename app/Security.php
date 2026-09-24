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
        header('X-Frame-Options: SAMEORIGIN');
        header('Referrer-Policy: strict-origin-when-cross-origin');
        header("Content-Security-Policy: default-src 'self'; img-src 'self' data: blob:; media-src 'self' blob:; style-src 'self' 'unsafe-inline' https://fonts.googleapis.com; font-src 'self' https://fonts.gstatic.com data:; script-src 'self'; connect-src 'self'; frame-ancestors 'self'");
    }

    public static function ensureCsrfToken(bool $force = false): string
    {
        if ($force || empty($_SESSION['csrf_token'])) {
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
        $session = $_SESSION['csrf_token'] ?? '';
        if (!is_string($token) || $token === '' || $session === '') {
            return false;
        }
        return hash_equals($session, $token);
    }

    public static function requireCsrfFromRequest(): void
    {
        $token = $_SERVER['HTTP_X_CSRF_TOKEN'] ?? ($_POST['_csrf'] ?? null);
        if (!self::verifyCsrf(is_string($token) ? $token : null)) {
            Response::jsonError('Invalid CSRF token.', 403, 'csrf');
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
