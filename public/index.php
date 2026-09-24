<?php

declare(strict_types=1);

$GLOBALS['ns_config'] = require dirname(__DIR__) . '/app/bootstrap.php';

\Northstar\View::render('home', [
    'pageTitle' => 'Home',
    'bodyClass' => 'page-home',
]);
