<?php

declare(strict_types=1);

/**
 * Public hosted loading screen page.
 * Preferred: /load.php?t=TOKEN
 * Also: /load?t=TOKEN  (rewrite) · /load?=TOKEN · /load?TOKEN
 */

$config = require dirname(__DIR__) . '/app/bootstrap.php';

try {
    $token = \Northstar\HostedLoad::tokenFromRequest();
} catch (\Throwable) {
    http_response_code(404);
    header('Content-Type: text/plain; charset=utf-8');
    echo "Loading screen not found.\n\nRegenerate the resource from the builder after running database/migrations/002_publish_token.sql.";
    exit;
}

$project = \Northstar\HostedLoad::findByToken($token);
if (!$project) {
    http_response_code(404);
    header('Content-Type: text/plain; charset=utf-8');
    echo "Loading screen not found for this token.\n\nOpen the project in the builder and click Generate Resource again.";
    exit;
}

$configPath = '/api/load/config.php?t=' . rawurlencode($token);
$serverName = htmlspecialchars(
    (string) (($project['config']['server']['name'] ?? null) ?: $project['name'] ?: 'Loading'),
    ENT_QUOTES | ENT_SUBSTITUTE,
    'UTF-8'
);

header('Content-Type: text/html; charset=utf-8');
header('Cache-Control: no-store, max-age=0');
header('X-Content-Type-Options: nosniff');
header('Referrer-Policy: no-referrer');

?><!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title><?= $serverName ?></title>
  <link rel="stylesheet" href="/hosted/css/loadscreen.css">
</head>
<body data-config-url="<?= htmlspecialchars($configPath, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>">
  <div id="stage">
    <div id="background"></div>
    <div id="overlay"></div>
    <div id="components"></div>
    <div id="watermarks" class="ns-watermarks" aria-hidden="true"></div>
  </div>
  <audio id="music" preload="auto"></audio>
  <div id="yt-host" class="yt-host" aria-hidden="true"><div id="yt-player"></div></div>
  <script src="/hosted/js/runtime.js"></script>
  <script src="/hosted/js/fivem.js"></script>
</body>
</html>
