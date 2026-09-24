<?php

declare(strict_types=1);

$config = require dirname(__DIR__, 3) . '/app/bootstrap.php';

\Northstar\Auth::requireLogin();
\Northstar\Security::requireCsrfFromRequest();

$userId = (int) \Northstar\Auth::userId();

$raw = file_get_contents('php://input') ?: '';
$data = json_decode($raw, true);
$projectId = (int) ($data['project_id'] ?? ($_POST['project_id'] ?? 0));

try {
    $result = \Northstar\ResourceGenerator::generate($projectId, $userId, $config);
    \Northstar\Response::jsonOk($result);
} catch (\InvalidArgumentException $e) {
    \Northstar\Response::jsonError($e->getMessage(), 422, 'validation');
} catch (\Throwable $e) {
    \Northstar\Logger::error('generate', ['error' => $e->getMessage()]);
    \Northstar\Response::jsonError($e->getMessage(), 400, 'generate');
}
