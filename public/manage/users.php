<?php

declare(strict_types=1);

$config = require dirname(__DIR__, 2) . '/app/bootstrap.php';
$GLOBALS['ns_config'] = $config;

\Northstar\Manage::requireStaff();
$users = \Northstar\Manage::listUsers(200);

\Northstar\View::render('manage/users', [
    'pageTitle' => 'Manage users',
    'bodyClass' => 'page-manage',
    'users' => $users,
    'role' => \Northstar\Manage::role(),
    'extraJs' => '/assets/js/manage-users.js',
]);
