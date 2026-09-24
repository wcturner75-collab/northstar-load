<?php

declare(strict_types=1);

$config = require dirname(__DIR__, 3) . '/app/bootstrap.php';

\Northstar\Auth::requireLogin();
\Northstar\Security::requireCsrfFromRequest();

$userId = (int) \Northstar\Auth::userId();
$id = (int) ($_POST['id'] ?? ($_GET['id'] ?? 0));

$raw = file_get_contents('php://input') ?: '';
$data = json_decode($raw, true);
if (!is_array($data)) {
    // fallback form
    $data = [
        'id' => $id,
        'config' => json_decode((string) ($_POST['config'] ?? '{}'), true),
    ];
}
$id = (int) ($data['id'] ?? $id);
$configDoc = $data['config'] ?? null;
if (!is_array($configDoc)) {
    \Northstar\Response::jsonError('Invalid config payload.', 422, 'validation');
}

try {
    $project = \Northstar\Project::saveConfig($id, $userId, $configDoc, false, $config);
    \Northstar\Response::jsonOk([
        'savedAt' => $project['last_saved_at'] ?? date('c'),
        'configVersion' => (int) ($project['config_version'] ?? 0),
    ]);
} catch (\InvalidArgumentException $e) {
    \Northstar\Response::jsonError($e->getMessage(), 422, 'validation');
} catch (\Throwable $e) {
    \Northstar\Logger::error('save project', ['error' => $e->getMessage()]);
    \Northstar\Response::jsonError('Save failed.', 400, 'save');
}
