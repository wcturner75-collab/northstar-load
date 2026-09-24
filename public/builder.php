<?php

declare(strict_types=1);

$config = require dirname(__DIR__) . '/app/bootstrap.php';
$GLOBALS['ns_config'] = $config;

\Northstar\Auth::requireLogin();
$userId = (int) \Northstar\Auth::userId();
$id = (int) ($_GET['id'] ?? 0);
$project = \Northstar\Project::findOwned($id, $userId);
if (!$project) {
    http_response_code(404);
    echo 'Project not found.';
    exit;
}

\Northstar\View::renderBare('builder', [
    'project' => $project,
    'csrf' => \Northstar\Security::csrfToken(),
    'config' => $config,
    'entitlements' => \Northstar\Entitlement::limits($userId, 'load', $config),
    'editorMode' => (string) ((\Northstar\Auth::user()['editor_mode'] ?? 'simple')),
]);
