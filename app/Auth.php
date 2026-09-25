<?php

declare(strict_types=1);

namespace Northstar;

use PDO;

final class Auth
{
    public static function userId(): ?int
    {
        $id = Session::get('user_id');
        return is_int($id) ? $id : (is_numeric($id) ? (int) $id : null);
    }

    public static function check(): bool
    {
        return self::userId() !== null;
    }

    public static function requireLogin(): void
    {
        if (!self::check()) {
            if (self::wantsJson()) {
                Response::jsonError('Authentication required.', 401, 'auth');
            }
            Response::redirect('/login');
        }
    }

    public static function guestOnly(): void
    {
        if (self::check()) {
            Response::redirect('/dashboard');
        }
    }

    /** @return array<string,mixed>|null */
    public static function user(): ?array
    {
        $id = self::userId();
        if ($id === null) {
            return null;
        }
        static $cache = null;
        static $cacheId = null;
        if ($cacheId === $id && is_array($cache)) {
            return $cache;
        }
        $stmt = Database::pdo()->prepare('SELECT id, email, username, status, editor_mode, role, created_at FROM users WHERE id = ? LIMIT 1');
        $stmt->execute([$id]);
        $row = $stmt->fetch();
        $cache = $row ?: null;
        $cacheId = $id;
        return $cache;
    }

    /**
     * @param 'free'|'standard'|'pro' $plan
     * @param 'simple'|'advanced' $editorMode
     */
    public static function register(
        string $email,
        string $username,
        string $password,
        string $plan = 'free',
        string $editorMode = 'simple'
    ): array {
        $email = strtolower(trim($email));
        $username = trim($username);
        $plan = strtolower(trim($plan));
        $editorMode = strtolower(trim($editorMode));

        if (!filter_var($email, FILTER_VALIDATE_EMAIL) || strlen($email) > 255) {
            throw new \InvalidArgumentException('Invalid email address.');
        }
        if (!preg_match('/^[a-zA-Z0-9_]{3,32}$/', $username)) {
            throw new \InvalidArgumentException('Username must be 3–32 characters (letters, numbers, underscore).');
        }
        if (strlen($password) < 8 || strlen($password) > 128) {
            throw new \InvalidArgumentException('Password must be 8–128 characters.');
        }
        if (!in_array($plan, ['free', 'standard', 'pro'], true)) {
            throw new \InvalidArgumentException('Invalid plan.');
        }
        // Until billing is enabled, force Free regardless of form POST.
        $cfg = $GLOBALS['ns_config'] ?? [];
        if (!Entitlement::isPlanSelectable($plan, is_array($cfg) ? $cfg : [])) {
            $plan = 'free';
        }
        if (!in_array($editorMode, ['simple', 'advanced'], true)) {
            throw new \InvalidArgumentException('Invalid editor mode.');
        }

        $pdo = Database::pdo();
        $check = $pdo->prepare('SELECT id FROM users WHERE email = ? OR username = ? LIMIT 1');
        $check->execute([$email, $username]);
        if ($check->fetch()) {
            throw new \InvalidArgumentException('Email or username already in use.');
        }

        $hash = password_hash($password, PASSWORD_DEFAULT);
        $pdo->beginTransaction();
        try {
            $ins = $pdo->prepare('INSERT INTO users (email, username, password_hash, editor_mode) VALUES (?, ?, ?, ?)');
            $ins->execute([$email, $username, $hash, $editorMode]);
            $userId = (int) $pdo->lastInsertId();

            $ent = $pdo->prepare(
                'INSERT INTO entitlements (user_id, product_key, plan_key, source, meta_json)
                 VALUES (?, ?, ?, ?, ?)'
            );
            $ent->execute([
                $userId,
                'load',
                $plan,
                'signup',
                json_encode(['editor_mode' => $editorMode], JSON_UNESCAPED_SLASHES),
            ]);

            $pdo->commit();
        } catch (\Throwable $e) {
            $pdo->rollBack();
            Logger::error('Register failed', ['error' => $e->getMessage()]);
            throw new \RuntimeException('Could not create account.');
        }

        self::loginUser($userId);
        return self::user() ?? ['id' => $userId];
    }

    public static function setEditorMode(int $userId, string $mode): void
    {
        $mode = strtolower(trim($mode));
        if (!in_array($mode, ['simple', 'advanced'], true)) {
            throw new \InvalidArgumentException('Invalid editor mode.');
        }
        $stmt = Database::pdo()->prepare('UPDATE users SET editor_mode = ? WHERE id = ?');
        $stmt->execute([$mode, $userId]);
    }

    public static function attemptLogin(string $email, string $password, array $config): bool
    {
        $email = strtolower(trim($email));
        $ipHash = Security::hashIp(Security::clientIp());

        if (self::isRateLimited($email, $ipHash, $config)) {
            self::recordAttempt($email, $ipHash, false);
            throw new \RuntimeException('Too many login attempts. Try again later.');
        }

        $stmt = Database::pdo()->prepare('SELECT id, password_hash, status FROM users WHERE email = ? LIMIT 1');
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        $ok = false;
        if ($user && ($user['status'] ?? '') === 'active') {
            $ok = password_verify($password, $user['password_hash']);
        }

        self::recordAttempt($email, $ipHash, $ok);

        if (!$ok) {
            return false;
        }

        self::loginUser((int) $user['id']);
        return true;
    }

    public static function logout(): void
    {
        $sid = session_id();
        if ($sid) {
            try {
                $stmt = Database::pdo()->prepare('UPDATE user_sessions SET revoked_at = NOW(3) WHERE session_id = ?');
                $stmt->execute([$sid]);
            } catch (\Throwable $e) {
                // ignore DB errors on logout
            }
        }
        Session::destroy();
        $config = $GLOBALS['ns_config'] ?? [];
        if (is_array($config)) {
            Session::start($config);
        } elseif (session_status() !== PHP_SESSION_ACTIVE) {
            session_start();
        }
        Security::ensureCsrfToken(true);
    }

    private static function loginUser(int $userId): void
    {
        Session::regenerate();
        Session::set('user_id', $userId);

        $pdo = Database::pdo();
        $stmt = $pdo->prepare(
            'INSERT INTO user_sessions (user_id, session_id, ip_hash, user_agent_hash, expires_at)
             VALUES (?, ?, ?, ?, DATE_ADD(NOW(3), INTERVAL 2 HOUR))'
        );
        $stmt->execute([
            $userId,
            session_id(),
            Security::hashIp(Security::clientIp()),
            hash('sha256', Security::userAgent()),
        ]);
    }

    /** @param array<string,mixed> $config */
    private static function isRateLimited(string $email, string $ipHash, array $config): bool
    {
        $max = (int) ($config['security']['login_max_attempts'] ?? 8);
        $window = (int) ($config['security']['login_window_seconds'] ?? 900);
        $stmt = Database::pdo()->prepare(
            'SELECT COUNT(*) FROM login_attempts
             WHERE succeeded = 0 AND created_at >= DATE_SUB(NOW(3), INTERVAL ? SECOND)
               AND (email_normalized = ? OR ip_hash = ?)'
        );
        $stmt->execute([$window, $email, $ipHash]);
        return (int) $stmt->fetchColumn() >= $max;
    }

    private static function recordAttempt(string $email, string $ipHash, bool $ok): void
    {
        $stmt = Database::pdo()->prepare(
            'INSERT INTO login_attempts (email_normalized, ip_hash, succeeded) VALUES (?, ?, ?)'
        );
        $stmt->execute([$email, $ipHash, $ok ? 1 : 0]);
    }

    public static function wantsJson(): bool
    {
        $accept = $_SERVER['HTTP_ACCEPT'] ?? '';
        $uri = $_SERVER['REQUEST_URI'] ?? '';
        return str_contains($accept, 'application/json') || str_contains($uri, '/api/');
    }
}
