<?php

declare(strict_types=1);

namespace Northstar;

/**
 * Management-team helpers (role: manager | admin).
 */
final class Manage
{
    public static function role(?array $user = null): string
    {
        $user = $user ?? Auth::user();
        $role = strtolower((string) ($user['role'] ?? 'user'));
        return in_array($role, ['user', 'manager', 'admin'], true) ? $role : 'user';
    }

    public static function isStaff(?array $user = null): bool
    {
        $role = self::role($user);
        return $role === 'manager' || $role === 'admin';
    }

    public static function requireStaff(): void
    {
        Auth::requireLogin();
        if (!self::isStaff()) {
            if (Auth::wantsJson()) {
                Response::jsonError('Management access required.', 403, 'forbidden');
            }
            http_response_code(403);
            echo 'Management access required.';
            exit;
        }
    }

    /** @return array{users:int,projects:int,media:int,builds:int,managers:int} */
    public static function stats(): array
    {
        $pdo = Database::pdo();
        return [
            'users' => (int) $pdo->query('SELECT COUNT(*) FROM users')->fetchColumn(),
            'projects' => (int) $pdo->query("SELECT COUNT(*) FROM projects WHERE status != 'archived'")->fetchColumn(),
            'media' => (int) $pdo->query('SELECT COUNT(*) FROM media WHERE deleted_at IS NULL')->fetchColumn(),
            'builds' => (int) $pdo->query("SELECT COUNT(*) FROM builds WHERE status = 'ready'")->fetchColumn(),
            'managers' => (int) $pdo->query("SELECT COUNT(*) FROM users WHERE role IN ('manager','admin')")->fetchColumn(),
        ];
    }

    /** @return list<array<string,mixed>> */
    public static function listUsers(int $limit = 100): array
    {
        $limit = max(1, min(500, $limit));
        $stmt = Database::pdo()->query(
            "SELECT u.id, u.email, u.username, u.status, u.role, u.editor_mode, u.created_at,
                    (SELECT e.plan_key FROM entitlements e
                     WHERE e.user_id = u.id AND e.product_key = 'load'
                     ORDER BY e.id DESC LIMIT 1) AS plan_key
             FROM users u
             ORDER BY u.created_at DESC
             LIMIT {$limit}"
        );
        return $stmt->fetchAll() ?: [];
    }

    public static function setUserStatus(int $userId, string $status): void
    {
        if (!in_array($status, ['active', 'disabled', 'pending'], true)) {
            throw new \InvalidArgumentException('Invalid status.');
        }
        $stmt = Database::pdo()->prepare('UPDATE users SET status = ? WHERE id = ?');
        $stmt->execute([$status, $userId]);
    }

    public static function setUserRole(int $userId, string $role): void
    {
        if (!in_array($role, ['user', 'manager', 'admin'], true)) {
            throw new \InvalidArgumentException('Invalid role.');
        }
        // Only admins can grant admin
        if ($role === 'admin' && self::role() !== 'admin') {
            throw new \RuntimeException('Only admins can assign admin.');
        }
        $stmt = Database::pdo()->prepare('UPDATE users SET role = ? WHERE id = ?');
        $stmt->execute([$role, $userId]);
    }
}
