<?php

declare(strict_types=1);

$config = require dirname(__DIR__, 3) . '/app/bootstrap.php';

\Northstar\Auth::requireLogin();
\Northstar\Security::requireCsrfFromRequest();

$userId = (int) \Northstar\Auth::userId();

if (empty($_FILES['file'])) {
    \Northstar\Response::jsonError('No file uploaded.', 422, 'validation');
}

try {
    $media = \Northstar\MediaManager::upload($userId, $_FILES['file'], $config);
    \Northstar\Response::jsonOk(['media' => [
        'id' => (int) $media['id'],
        'original_name' => $media['original_name'],
        'kind' => $media['kind'],
        'mime_type' => $media['mime_type'],
        'size_bytes' => (int) $media['size_bytes'],
        'width' => $media['width'],
        'height' => $media['height'],
    ]]);
} catch (\InvalidArgumentException $e) {
    \Northstar\Response::jsonError($e->getMessage(), 422, 'validation');
} catch (\Throwable $e) {
    \Northstar\Logger::error('upload', ['error' => $e->getMessage()]);
    \Northstar\Response::jsonError($e->getMessage(), 400, 'upload');
}
