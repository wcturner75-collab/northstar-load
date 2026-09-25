<?php

declare(strict_types=1);

$config = require dirname(__DIR__) . '/app/bootstrap.php';
$GLOBALS['ns_config'] = $config;

\Northstar\Auth::guestOnly();

$error = null;
$email = '';
$username = '';
$plan = 'free';
$editorMode = 'simple';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!\Northstar\Security::verifyCsrf($_POST['_csrf'] ?? null)) {
        $error = 'Invalid session token. Refresh and try again.';
    } else {
        $email = (string) ($_POST['email'] ?? '');
        $username = (string) ($_POST['username'] ?? '');
        $password = (string) ($_POST['password'] ?? '');
        // Billing off → Free only (ignore paid plan POSTs).
        $plan = \Northstar\Entitlement::isPlanSelectable((string) ($_POST['plan'] ?? 'free'), $config)
            ? (string) ($_POST['plan'] ?? 'free')
            : 'free';
        $editorMode = (string) ($_POST['editor_mode'] ?? 'simple');
        try {
            \Northstar\Auth::register($email, $username, $password, $plan, $editorMode);
            \Northstar\Response::redirect('/dashboard');
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
    'bodyClass' => 'page-auth page-register',
    'error' => $error,
    'email' => $email,
    'username' => $username,
    'plan' => $plan,
    'editorMode' => $editorMode,
    'billingEnabled' => \Northstar\Entitlement::billingEnabled($config),
    'catalog' => \Northstar\Entitlement::catalog($config),
]);
