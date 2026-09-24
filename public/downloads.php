<?php

declare(strict_types=1);

$config = require dirname(__DIR__) . '/app/bootstrap.php';
$GLOBALS['ns_config'] = $config;

\Northstar\Auth::requireLogin();
$userId = (int) \Northstar\Auth::userId();

\Northstar\View::render('downloads', [
    'pageTitle' => 'Downloads',
    'builds' => \Northstar\Build::listForUser($userId),
    'extraJs' => '/assets/js/downloads.js',
]);
