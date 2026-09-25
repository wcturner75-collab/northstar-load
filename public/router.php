<?php

declare(strict_types=1);

/**
 * Router for PHP built-in server:
 * php -S 127.0.0.1:8080 -t public public/router.php
 */

$path = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';
$file = __DIR__ . $path;

if ($path !== '/' && is_file($file)) {
    return false;
}

// Pretty hosted load URL: /load?t=TOKEN
if ($path === '/load' || $path === '/load/') {
    require __DIR__ . '/load.php';
    return true;
}

if ($path === '/system-status' || $path === '/system-status/') {
    require __DIR__ . '/system-status.php';
    return true;
}

if ($path === '/manage' || $path === '/manage/') {
    require __DIR__ . '/manage/index.php';
    return true;
}

if (str_ends_with($path, '.php') && is_file($file)) {
    require $file;
    return true;
}

// Directory index for /manage/users etc. when pretty paths used
if (is_dir($file) && is_file(rtrim($file, '/') . '/index.php')) {
    require rtrim($file, '/') . '/index.php';
    return true;
}

if ($path === '/' || $path === '') {
    require __DIR__ . '/index.php';
    return true;
}

http_response_code(404);
echo 'Not found';
return true;
