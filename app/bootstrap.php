<?php

declare(strict_types=1);

define('NORTHSTAR_ROOT', dirname(__DIR__));

$configFile = NORTHSTAR_ROOT . '/config/config.php';
if (!is_file($configFile)) {
    http_response_code(500);
    header('Content-Type: text/html; charset=utf-8');
    echo '<!DOCTYPE html><html><head><title>Configuration missing</title></head><body style="font-family:sans-serif;background:#040910;color:#eaf3fa;padding:2rem">';
    echo '<h1>Configuration missing</h1>';
    echo '<p>Copy <code>config/config.example.php</code> to <code>config/config.php</code> and set database credentials.</p>';
    echo '</body></html>';
    exit;
}

/** @var array $config */
$config = require $configFile;

$config['paths'] = [
    'root' => NORTHSTAR_ROOT,
    'public' => NORTHSTAR_ROOT . '/public',
    'storage' => NORTHSTAR_ROOT . '/storage',
    'uploads' => NORTHSTAR_ROOT . '/storage/uploads',
    'builds' => NORTHSTAR_ROOT . '/storage/builds',
    'build_temp' => NORTHSTAR_ROOT . '/storage/build-temp',
    'logs' => NORTHSTAR_ROOT . '/storage/logs',
    'templates' => NORTHSTAR_ROOT . '/templates',
    'fivem_template' => NORTHSTAR_ROOT . '/templates/fivem-loadscreen',
];

date_default_timezone_set($config['app']['timezone'] ?? 'UTC');

spl_autoload_register(static function (string $class): void {
    $prefix = 'Northstar\\';
    if (!str_starts_with($class, $prefix)) {
        return;
    }
    $relative = str_replace('\\', '/', substr($class, strlen($prefix)));
    $path = NORTHSTAR_ROOT . '/app/' . $relative . '.php';
    if (is_file($path)) {
        require $path;
    }
});

$GLOBALS['ns_db_ready'] = false;
try {
    \Northstar\Database::init($config);
    $GLOBALS['ns_db_ready'] = true;
} catch (\Throwable $e) {
    $GLOBALS['ns_db_ready'] = false;
    // Soft-fail: health page / redirect instead of hard crash
    if (!\Northstar\DbHealth::isExemptPath()) {
        header('Location: /system-status.php?reason=connection');
        exit;
    }
}

if ($GLOBALS['ns_db_ready']) {
    \Northstar\Session::start($config);
    \Northstar\DbHealth::guard($config);
}

\Northstar\Security::sendHeaders();

return $config;
