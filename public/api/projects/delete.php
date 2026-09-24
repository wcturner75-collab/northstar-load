<?php

declare(strict_types=1);

$config = require dirname(__DIR__, 3) . '/app/bootstrap.php';

\Northstar\Auth::requireLogin();
\Northstar\Security::requireCsrfFromRequest();

$userId = (int) \Northstar\Auth::userId();
$id = (int) ($_POST['id'] ?? 0);

try {
    \Northstar\Project::archive($id, $userId);
    \Northstar\Response::jsonOk(['archived' => true]);
} catch (\Throwable $e) {
    \Northstar\Response::jsonError($e->getMessage(), 404, 'not_found');
}
