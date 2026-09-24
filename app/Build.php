<?php

declare(strict_types=1);

namespace Northstar;

final class Build
{
    /** @return list<array<string,mixed>> */
    public static function listForUser(int $userId): array
    {
        $stmt = Database::pdo()->prepare(
            'SELECT b.id, b.build_token, b.resource_name, b.file_size, b.runtime_version, b.status,
                    b.created_at, b.expires_at, b.project_id, p.name AS project_name
             FROM builds b
             JOIN projects p ON p.id = b.project_id
             WHERE b.user_id = ? AND b.status IN (\'ready\', \'expired\')
             ORDER BY b.created_at DESC
             LIMIT 100'
        );
        $stmt->execute([$userId]);
        return $stmt->fetchAll();
    }

    /** @return array<string,mixed>|null */
    public static function findByTokenForUser(string $token, int $userId): ?array
    {
        if (!preg_match('/^[a-f0-9]{64}$/', $token)) {
            return null;
        }
        $stmt = Database::pdo()->prepare(
            'SELECT * FROM builds WHERE build_token = ? AND user_id = ? LIMIT 1'
        );
        $stmt->execute([$token, $userId]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public static function softDelete(int $buildId, int $userId, array $config): void
    {
        $stmt = Database::pdo()->prepare(
            'SELECT * FROM builds WHERE id = ? AND user_id = ? LIMIT 1'
        );
        $stmt->execute([$buildId, $userId]);
        $row = $stmt->fetch();
        if (!$row) {
            throw new \RuntimeException('Build not found.');
        }
        $path = Path::join($config['paths']['builds'], $row['file_path']);
        try {
            $abs = Path::assertInside($config['paths']['builds'], $path);
            if (is_file($abs)) {
                @unlink($abs);
            }
        } catch (\Throwable) {
            // continue marking deleted
        }
        $upd = Database::pdo()->prepare(
            "UPDATE builds SET status = 'deleted' WHERE id = ? AND user_id = ?"
        );
        $upd->execute([$buildId, $userId]);
    }
}
