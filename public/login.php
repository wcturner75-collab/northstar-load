<?php

declare(strict_types=1);

$config = require dirname(__DIR__) . '/app/bootstrap.php';
$GLOBALS['ns_config'] = $config;

\Northstar\Auth::guestOnly();

$error = null;
$email = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!\Northstar\Security::verifyCsrf($_POST['_csrf'] ?? null)) {
        $error = 'Invalid session token. Refresh and try again.';
    } else {
        $email = (string) ($_POST['email'] ?? '');
        $password = (string) ($_POST['password'] ?? '');
        try {
            if (\Northstar\Auth::attemptLogin($email, $password, $config)) {
                \Northstar\Response::redirect('/dashboard.php');
            }
            $error = 'Invalid email or password.';
        } catch (\Throwable $e) {
            $error = $e->getMessage();
        }
    }
}

\Northstar\View::render('login', [
    'pageTitle' => 'Login',
    'bodyClass' => 'page-auth',
    'error' => $error,
    'email' => $email,
]);
