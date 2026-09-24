<?php

declare(strict_types=1);

$config = require dirname(__DIR__, 3) . '/app/bootstrap.php';

header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store, max-age=0');
header('Access-Control-Allow-Origin: *');
header('X-Content-Type-Options: nosniff');

try {
    $token = \Northstar\HostedLoad::tokenFromRequest();
    $project = \Northstar\HostedLoad::findByToken($token);
    if (!$project) {
        http_response_code(404);
        echo json_encode(['ok' => false, 'error' => ['message' => 'Not found']]);
        exit;
    }
    $runtime = \Northstar\HostedLoad::runtimeConfig($project, $config);
    echo json_encode($runtime, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
} catch (\Throwable $e) {
    http_response_code(400);
    echo json_encode(['ok' => false, 'error' => ['message' => 'Could not load config']]);
}
exit;
