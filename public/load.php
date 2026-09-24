<?php

declare(strict_types=1);

/**
 * Public hosted loading screen page.
 * URL: https://load.northstarscripts.us/load?t=TOKEN
 * Also accepts: /load?=TOKEN  and  /load?TOKEN
 */

$config = require dirname(__DIR__) . '/app/bootstrap.php';

try {
    $token = \Northstar\HostedLoad::tokenFromRequest();
} catch (\Throwable) {
    http_response_code(404);
    header('Content-Type: text/plain; charset=utf-8');
    echo 'Loading screen not found.';
    exit;
}

$project = \Northstar\HostedLoad::findByToken($token);
if (!$project) {
    http_response_code(404);
    header('Content-Type: text/plain; charset=utf-8');
    echo 'Loading screen not found.';
    exit;
}

$base = \Northstar\HostedLoad::loadBaseUrl($config);
$configUrl = $base . '/api/load/config.php?t=' . rawurlencode($token);
$serverName = htmlspecialchars(
    (string) (($project['config']['server']['name'] ?? null) ?: $project['name'] ?: 'Loading'),
    ENT_QUOTES | ENT_SUBSTITUTE,
    'UTF-8'
);
$configUrlJs = json_encode($configUrl, JSON_UNESCAPED_SLASHES);

header('Content-Type: text/html; charset=utf-8');
header('Cache-Control: no-store, max-age=0');
header('X-Content-Type-Options: nosniff');
header('Referrer-Policy: no-referrer');
// Allow embedding in FiveM CEF loadscreen
header('Content-Security-Policy: default-src \'self\' https: data: blob:; script-src \'self\' \'unsafe-inline\' https://www.youtube.com https://www.youtube-nocookie.com https://www.google.com; frame-src https://www.youtube.com https://www.youtube-nocookie.com; media-src \'self\' https: blob: data:; img-src \'self\' https: data: blob:; style-src \'self\' \'unsafe-inline\' https://fonts.googleapis.com; font-src \'self\' https://fonts.gstatic.com data:;');

?><!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title><?= $serverName ?></title>
  <link rel="stylesheet" href="/hosted/css/loadscreen.css">
</head>
<body>
  <div id="stage">
    <div id="background"></div>
    <div id="overlay"></div>
    <div id="components"></div>
    <div id="watermarks" class="ns-watermarks" aria-hidden="true"></div>
  </div>
  <audio id="music" preload="auto"></audio>
  <div id="yt-host" class="yt-host" aria-hidden="true"><div id="yt-player"></div></div>
  <script>window.NS_LOAD_CONFIG_URL = <?= $configUrlJs ?>;</script>
  <script src="/hosted/js/runtime.js"></script>
  <script src="/hosted/js/fivem.js"></script>
</body>
</html>
