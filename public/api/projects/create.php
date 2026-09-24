<?php

declare(strict_types=1);

$config = require dirname(__DIR__, 3) . '/app/bootstrap.php';

\Northstar\Auth::requireLogin();
\Northstar\Security::requireCsrfFromRequest();

$userId = (int) \Northstar\Auth::userId();
$name = (string) ($_POST['name'] ?? '');
$resource = (string) ($_POST['resource_name'] ?? '');
$templateId = isset($_POST['template_id']) && $_POST['template_id'] !== ''
    ? (int) $_POST['template_id']
    : null;

try {
    $project = \Northstar\Project::create($userId, $name, $resource, $templateId, $config);
    \Northstar\Response::jsonOk([
        'project' => [
            'id' => (int) $project['id'],
            'name' => $project['name'],
            'resource_name' => $project['resource_name'],
        ],
    ]);
} catch (\InvalidArgumentException $e) {
    \Northstar\Response::jsonError($e->getMessage(), 422, 'validation');
} catch (\Throwable $e) {
    \Northstar\Logger::error('create project', ['error' => $e->getMessage()]);
    \Northstar\Response::jsonError($e->getMessage(), 400, 'create');
}
