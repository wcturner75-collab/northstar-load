<?php

declare(strict_types=1);

$config = require dirname(__DIR__, 3) . '/app/bootstrap.php';

\Northstar\Auth::requireLogin();
$userId = (int) \Northstar\Auth::userId();
$id = (int) ($_GET['id'] ?? 0);

$media = \Northstar\MediaManager::findOwned($id, $userId);
if (!$media) {
    http_response_code(404);
    exit;
}

try {
    $abs = \Northstar\MediaManager::absolutePath($media, $config);
} catch (\Throwable) {
    http_response_code(404);
    exit;
}

if (!is_file($abs)) {
    http_response_code(404);
    exit;
}

header('Content-Type: ' . $media['mime_type']);
header('Content-Length: ' . (string) filesize($abs));
header('Cache-Control: private, max-age=3600');
header('X-Content-Type-Options: nosniff');
readfile($abs);
exit;
