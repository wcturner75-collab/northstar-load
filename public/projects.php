<?php

declare(strict_types=1);

$config = require dirname(__DIR__) . '/app/bootstrap.php';
$GLOBALS['ns_config'] = $config;

\Northstar\Auth::requireLogin();
$userId = (int) \Northstar\Auth::userId();

\Northstar\View::render('projects', [
    'pageTitle' => 'Projects',
    'projects' => \Northstar\Project::listForUser($userId),
    'templates' => \Northstar\Template::listActive(),
    'showCreate' => isset($_GET['new']),
    'extraJs' => '/assets/js/projects.js',
]);
