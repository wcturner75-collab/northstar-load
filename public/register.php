<?php

declare(strict_types=1);

$config = require dirname(__DIR__) . '/app/bootstrap.php';
$GLOBALS['ns_config'] = $config;

\Northstar\Auth::guestOnly();

$error = null;
$email = '';
$username = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!\Northstar\Security::verifyCsrf($_POST['_csrf'] ?? null)) {
        $error = 'Invalid session token. Refresh and try again.';
    } else {
        $email = (string) ($_POST['email'] ?? '');
        $username = (string) ($_POST['username'] ?? '');
        $password = (string) ($_POST['password'] ?? '');
        try {
            \Northstar\Auth::register($email, $username, $password);
            \Northstar\Response::redirect('/dashboard.php');
        } catch (\InvalidArgumentException $e) {
            $error = $e->getMessage();
        } catch (\Throwable $e) {
            \Northstar\Logger::error('Register page error', ['error' => $e->getMessage()]);
            $error = 'Could not create account.';
        }
    }
}

\Northstar\View::render('register', [
    'pageTitle' => 'Register',
    'bodyClass' => 'page-auth',
    'error' => $error,
    'email' => $email,
    'username' => $username,
]);
