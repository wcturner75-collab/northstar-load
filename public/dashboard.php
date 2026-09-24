<?php

declare(strict_types=1);

$config = require dirname(__DIR__) . '/app/bootstrap.php';
$GLOBALS['ns_config'] = $config;

\Northstar\Auth::requireLogin();
$user = \Northstar\Auth::user();
$userId = (int) $user['id'];

$projects = array_slice(\Northstar\Project::listForUser($userId), 0, 8);
$builds = array_slice(\Northstar\Build::listForUser($userId), 0, 8);
$limits = \Northstar\Entitlement::limits($userId, 'load', $config);

$pdo = \Northstar\Database::pdo();
$s1 = $pdo->prepare("SELECT COUNT(*) FROM projects WHERE user_id=? AND product_key='load' AND status!='archived'");
$s1->execute([$userId]);
$s2 = $pdo->prepare('SELECT COUNT(*) FROM media WHERE user_id=? AND deleted_at IS NULL');
$s2->execute([$userId]);
$s3 = $pdo->prepare("SELECT COUNT(*) FROM builds WHERE user_id=? AND status='ready'");
$s3->execute([$userId]);
$stats = [
    'projects' => (int) $s1->fetchColumn(),
    'media' => (int) $s2->fetchColumn(),
    'builds' => (int) $s3->fetchColumn(),
];

\Northstar\View::render('dashboard', [
    'pageTitle' => 'Dashboard',
    'projects' => $projects,
    'builds' => $builds,
    'stats' => $stats,
    'plan' => $limits['plan'],
]);
