<?php

declare(strict_types=1);

$config = require dirname(__DIR__) . '/app/bootstrap.php';
$GLOBALS['ns_config'] = $config;

\Northstar\Auth::requireLogin();
$userId = (int) \Northstar\Auth::userId();
$limits = \Northstar\Entitlement::limits($userId, 'load', $config);

\Northstar\View::render('account', [
    'pageTitle' => 'Account',
    'limits' => $limits,
    'extraJs' => '/assets/js/account.js',
]);
