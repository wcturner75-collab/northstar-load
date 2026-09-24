<?php

declare(strict_types=1);

namespace Northstar;

final class Entitlement
{
    /** @param array<string,mixed> $config */
    public static function planFor(int $userId, string $productKey, array $config): string
    {
        $stmt = Database::pdo()->prepare(
            'SELECT plan_key FROM entitlements
             WHERE user_id = ? AND product_key = ?
               AND (ends_at IS NULL OR ends_at > NOW(3))
             ORDER BY FIELD(plan_key, \'pro\', \'standard\', \'free\'), id DESC
             LIMIT 1'
        );
        $stmt->execute([$userId, $productKey]);
        $plan = $stmt->fetchColumn();
        return is_string($plan) && $plan !== '' ? $plan : 'free';
    }

    /** @param array<string,mixed> $config @return array<string,int> */
    public static function limits(int $userId, string $productKey, array $config): array
    {
        $plan = self::planFor($userId, $productKey, $config);
        $limits = $config['entitlements'][$plan] ?? $config['entitlements']['free'];
        return [
            'plan' => $plan,
            'max_projects' => (int) $limits['max_projects'],
            'max_media' => (int) $limits['max_media'],
            'max_builds_per_day' => (int) $limits['max_builds_per_day'],
        ];
    }

    /** @param array<string,mixed> $config */
    public static function assertCanCreateProject(int $userId, array $config): void
    {
        $limits = self::limits($userId, 'load', $config);
        $stmt = Database::pdo()->prepare(
            "SELECT COUNT(*) FROM projects WHERE user_id = ? AND product_key = 'load' AND status != 'archived'"
        );
        $stmt->execute([$userId]);
        if ((int) $stmt->fetchColumn() >= $limits['max_projects']) {
            throw new \RuntimeException('Project limit reached for your plan (' . $limits['plan'] . ').');
        }
    }

    /** @param array<string,mixed> $config */
    public static function assertCanUploadMedia(int $userId, array $config): void
    {
        $limits = self::limits($userId, 'load', $config);
        $stmt = Database::pdo()->prepare(
            'SELECT COUNT(*) FROM media WHERE user_id = ? AND deleted_at IS NULL'
        );
        $stmt->execute([$userId]);
        if ((int) $stmt->fetchColumn() >= $limits['max_media']) {
            throw new \RuntimeException('Media limit reached for your plan (' . $limits['plan'] . ').');
        }
    }

    /** @param array<string,mixed> $config */
    public static function assertCanBuild(int $userId, array $config): void
    {
        $limits = self::limits($userId, 'load', $config);
        $stmt = Database::pdo()->prepare(
            'SELECT COUNT(*) FROM builds WHERE user_id = ? AND created_at >= DATE_SUB(NOW(3), INTERVAL 1 DAY)'
        );
        $stmt->execute([$userId]);
        if ((int) $stmt->fetchColumn() >= $limits['max_builds_per_day']) {
            throw new \RuntimeException('Daily build limit reached for your plan (' . $limits['plan'] . ').');
        }
    }
}
