<?php

declare(strict_types=1);

$config = require dirname(__DIR__, 3) . '/app/bootstrap.php';

\Northstar\Auth::requireLogin();
$userId = (int) \Northstar\Auth::userId();

\Northstar\Response::jsonOk([
    'projects' => \Northstar\Project::listForUser($userId),
]);
