<?php

declare(strict_types=1);

/**
 * Expire old builds and delete ZIP files.
 * Cron: php scripts/expire_builds.php
 */

$config = require dirname(__DIR__) . '/app/bootstrap.php';

$pdo = \Northstar\Database::pdo();
$stmt = $pdo->query(
    "SELECT id, file_path FROM builds
     WHERE status = 'ready' AND expires_at IS NOT NULL AND expires_at < NOW(3)"
);
$rows = $stmt->fetchAll();
$n = 0;
foreach ($rows as $row) {
    $path = \Northstar\Path::join($config['paths']['builds'], $row['file_path']);
    try {
        $abs = \Northstar\Path::assertInside($config['paths']['builds'], $path);
        if (is_file($abs)) {
            @unlink($abs);
        }
    } catch (\Throwable) {
        // continue
    }
    $upd = $pdo->prepare("UPDATE builds SET status = 'expired' WHERE id = ?");
    $upd->execute([(int) $row['id']]);
    $n++;
}

echo "Expired {$n} builds.\n";
