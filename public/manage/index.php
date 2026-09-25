<?php

declare(strict_types=1);

$config = require dirname(__DIR__, 2) . '/app/bootstrap.php';
$GLOBALS['ns_config'] = $config;

\Northstar\Manage::requireStaff();
$stats = \Northstar\Manage::stats();

\Northstar\View::render('manage/dashboard', [
    'pageTitle' => 'Management',
    'bodyClass' => 'page-manage',
    'stats' => $stats,
    'role' => \Northstar\Manage::role(),
]);
