<?php

declare(strict_types=1);

$config = require dirname(__DIR__, 3) . '/app/bootstrap.php';

header('X-Content-Type-Options: nosniff');
header('Access-Control-Allow-Origin: *');

try {
    $token = \Northstar\HostedLoad::tokenFromRequest();
} catch (\Throwable) {
    http_response_code(404);
    exit;
}

$project = \Northstar\HostedLoad::findByToken($token);
if (!$project) {
    http_response_code(404);
    exit;
}

$mediaId = (int) ($_GET['id'] ?? 0);
if ($mediaId <= 0) {
    http_response_code(404);
    exit;
}

$allowed = \Northstar\HostedLoad::referencedMediaIds(
    is_array($project['config'] ?? null) ? $project['config'] : []
);
if (!in_array($mediaId, $allowed, true)) {
    http_response_code(403);
    exit;
}

$userId = (int) $project['user_id'];
$media = \Northstar\MediaManager::findOwned($mediaId, $userId);
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
header('Cache-Control: public, max-age=3600');
readfile($abs);
exit;
