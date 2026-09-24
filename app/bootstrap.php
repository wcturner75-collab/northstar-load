<?php

declare(strict_types=1);

define('NORTHSTAR_ROOT', dirname(__DIR__));

$configFile = NORTHSTAR_ROOT . '/config/config.php';
if (!is_file($configFile)) {
    http_response_code(500);
    echo 'Configuration missing. Copy config/config.example.php to config/config.php.';
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

\Northstar\Database::init($config);
\Northstar\Session::start($config);
\Northstar\Security::sendHeaders();

return $config;
