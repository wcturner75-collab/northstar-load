<?php

declare(strict_types=1);

$config = require dirname(__DIR__, 3) . '/app/bootstrap.php';

\Northstar\Auth::requireLogin();
$userId = (int) \Northstar\Auth::userId();
$id = (int) ($_GET['id'] ?? 0);

$project = \Northstar\Project::findOwned($id, $userId);
if (!$project) {
    \Northstar\Response::jsonError('Not found.', 404, 'not_found');
}

\Northstar\Response::jsonOk([
    'project' => [
        'id' => (int) $project['id'],
        'name' => $project['name'],
        'resource_name' => $project['resource_name'],
        'theme_key' => $project['theme_key'],
        'config' => $project['config'],
        'last_saved_at' => $project['last_saved_at'],
    ],
]);
