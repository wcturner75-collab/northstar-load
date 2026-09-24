<?php

declare(strict_types=1);

$config = require dirname(__DIR__) . '/app/bootstrap.php';

\Northstar\Auth::requireLogin();
$userId = (int) \Northstar\Auth::userId();
$token = (string) ($_GET['build'] ?? '');

$build = \Northstar\Build::findByTokenForUser($token, $userId);
if (!$build || ($build['status'] ?? '') !== 'ready') {
    http_response_code(404);
    echo 'Build not found.';
    exit;
}

if (!empty($build['expires_at']) && strtotime((string) $build['expires_at']) < time()) {
    $upd = \Northstar\Database::pdo()->prepare("UPDATE builds SET status='expired' WHERE id=?");
    $upd->execute([(int) $build['id']]);
    http_response_code(410);
    echo 'Build expired.';
    exit;
}

$path = \Northstar\Path::join($config['paths']['builds'], (string) $build['file_path']);
try {
    $abs = \Northstar\Path::assertInside($config['paths']['builds'], $path);
} catch (\Throwable) {
    http_response_code(404);
    echo 'File missing.';
    exit;
}

if (!is_file($abs)) {
    http_response_code(404);
    echo 'File missing.';
    exit;
}

$filename = $build['resource_name'] . '.zip';
header('Content-Type: application/zip');
header('Content-Length: ' . (string) filesize($abs));
header('Content-Disposition: attachment; filename="' . $filename . '"');
header('X-Content-Type-Options: nosniff');
header('Cache-Control: no-store');
readfile($abs);
exit;
