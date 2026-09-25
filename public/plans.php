<?php

declare(strict_types=1);

$config = require dirname(__DIR__) . '/app/bootstrap.php';
$GLOBALS['ns_config'] = $config;

\Northstar\Auth::requireLogin();
$userId = (int) \Northstar\Auth::userId();
$limits = \Northstar\Entitlement::limits($userId, 'load', $config);

\Northstar\View::render('plans', [
    'pageTitle' => 'Plans',
    'limits' => $limits,
    'catalog' => \Northstar\Entitlement::catalog($config),
    'currentPlan' => $limits['plan'] ?? 'free',
    'billingEnabled' => \Northstar\Entitlement::billingEnabled($config),
    'extraJs' => '/assets/js/account.js',
]);
