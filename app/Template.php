<?php

declare(strict_types=1);

namespace Northstar;

final class Template
{
    /** @return list<array<string,mixed>> */
    public static function listActive(string $productKey = 'load'): array
    {
        $stmt = Database::pdo()->prepare(
            'SELECT id, slug, name, description, theme_key, preview_path, sort_order
             FROM templates
             WHERE product_key = ? AND is_active = 1
             ORDER BY sort_order ASC, id ASC'
        );
        $stmt->execute([$productKey]);
        return $stmt->fetchAll();
    }

    /** @return array<string,mixed>|null */
    public static function find(int $id): ?array
    {
        $stmt = Database::pdo()->prepare('SELECT * FROM templates WHERE id = ? AND is_active = 1 LIMIT 1');
        $stmt->execute([$id]);
        $row = $stmt->fetch();
        return $row ?: null;
    }
}
