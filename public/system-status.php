<?php

declare(strict_types=1);

/**
 * Shown when the database is unreachable or the schema is incomplete.
 * Does not require a healthy schema to render.
 */

$configFile = dirname(__DIR__) . '/config/config.php';
$config = is_file($configFile) ? require $configFile : [];

// Minimal autoload for health check only
define('NORTHSTAR_ROOT', dirname(__DIR__));
spl_autoload_register(static function (string $class): void {
    $prefix = 'Northstar\\';
    if (!str_starts_with($class, $prefix)) {
        return;
    }
    $path = NORTHSTAR_ROOT . '/app/' . str_replace('\\', '/', substr($class, strlen($prefix))) . '.php';
    if (is_file($path)) {
        require $path;
    }
});

$config['paths'] = $config['paths'] ?? [
    'logs' => NORTHSTAR_ROOT . '/storage/logs',
];

$reason = (string) ($_GET['reason'] ?? 'schema');
$status = [
    'ok' => false,
    'connected' => false,
    'missing_tables' => [],
    'missing_columns' => [],
    'error' => null,
];

try {
    if (!empty($config['db'])) {
        \Northstar\Database::init($config);
        $status = \Northstar\DbHealth::check($config);
    } else {
        $status['error'] = 'config.php is missing database settings.';
    }
} catch (\Throwable $e) {
    $status['error'] = $e->getMessage();
}

http_response_code($status['ok'] ? 200 : 503);
header('Content-Type: text/html; charset=utf-8');
header('Cache-Control: no-store');

$title = $status['ok'] ? 'System healthy' : 'System needs attention';
?><!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title><?= htmlspecialchars($title) ?> — Northstar Load</title>
  <link rel="stylesheet" href="/assets/css/app.css">
  <style>
    .sys-wrap { max-width: 720px; margin: 4rem auto; padding: 0 1.25rem; }
    .sys-card {
      border: 1px solid rgba(56,189,248,0.22);
      background: rgba(10,22,36,0.85);
      padding: 1.5rem;
    }
    .sys-card h1 { margin-top: 0; font-family: Syne, sans-serif; letter-spacing: 0.04em; }
    .sys-list { margin: 1rem 0; padding-left: 1.2rem; color: #7d96ab; }
    .sys-ok { color: #34d399; }
    .sys-bad { color: #f87171; }
    code { color: #7dd3fc; }
  </style>
</head>
<body>
  <div class="sys-wrap">
    <div class="sys-card">
      <p class="eyebrow">Northstar Load</p>
      <h1><?= htmlspecialchars($title) ?></h1>
      <?php if ($status['ok']): ?>
        <p class="sys-ok">Database connection and required tables look good.</p>
        <p><a class="btn btn-primary" href="/">Return home</a></p>
      <?php else: ?>
        <p class="sys-bad">
          <?php if (!$status['connected']): ?>
            Cannot reach the database<?= $reason === 'connection' ? '' : '' ?>.
          <?php else: ?>
            The database is reachable, but the schema is incomplete or out of date.
          <?php endif; ?>
        </p>
        <?php if (!empty($status['error'])): ?>
          <p><code><?= htmlspecialchars((string) $status['error']) ?></code></p>
        <?php endif; ?>
        <?php if ($status['missing_tables']): ?>
          <p>Missing tables:</p>
          <ul class="sys-list">
            <?php foreach ($status['missing_tables'] as $t): ?>
              <li><code><?= htmlspecialchars($t) ?></code></li>
            <?php endforeach; ?>
          </ul>
          <p>Import <code>database/schema.sql</code> in phpMyAdmin / MySQL.</p>
        <?php endif; ?>
        <?php if ($status['missing_columns']): ?>
          <p>Missing columns (run migrations):</p>
          <ul class="sys-list">
            <?php foreach ($status['missing_columns'] as $c): ?>
              <li><code><?= htmlspecialchars($c) ?></code></li>
            <?php endforeach; ?>
          </ul>
          <p>Apply files in <code>database/migrations/</code> — especially <code>004_user_role.sql</code> for management.</p>
        <?php endif; ?>
        <p style="margin-top:1.5rem">
          <a class="btn btn-primary" href="/system-status.php">Recheck</a>
        </p>
      <?php endif; ?>
    </div>
  </div>
</body>
</html>
