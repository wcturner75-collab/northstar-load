<?php
/** @var array $config */
/** @var array|null $user */
/** @var string $csrf */
/** @var string $pageTitle */
/** @var string $bodyClass */
$brand = $config['app']['brand'] ?? 'Northstar Load';
$appName = $config['app']['name'] ?? 'Northstar Load';
$pageTitle = $pageTitle ?? $appName;
$bodyClass = $bodyClass ?? '';
$adsense = is_array($config['adsense'] ?? null) ? $config['adsense'] : [];
$adsenseEnabled = !empty($adsense['enabled']) && !empty($adsense['client']);
$adsenseClient = (string) ($adsense['client'] ?? '');
?><!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="csrf-token" content="<?= \Northstar\Security::e($csrf) ?>">
  <?php if ($adsenseClient !== ''): ?>
  <meta name="google-adsense-account" content="<?= \Northstar\Security::e($adsenseClient) ?>">
  <?php endif; ?>
  <title><?= \Northstar\Security::e($pageTitle) ?> — <?= \Northstar\Security::e($brand) ?></title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Bebas+Neue&family=DM+Sans:wght@400;600;700&family=Orbitron:wght@500;700&family=Syne:wght@600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="/assets/css/app.css">
  <?php if (!empty($extraCss)): ?>
  <link rel="stylesheet" href="<?= \Northstar\Security::e($extraCss) ?>">
  <?php endif; ?>
  <?php if ($adsenseEnabled): ?>
  <script async src="https://pagead2.googlesyndication.com/pagead/js/adsbygoogle.js?client=<?= \Northstar\Security::e($adsenseClient) ?>" crossorigin="anonymous"></script>
  <?php endif; ?>
</head>
<body class="<?= \Northstar\Security::e($bodyClass) ?>">
<header class="site-header">
  <div class="shell header-inner">
    <a class="brand" href="<?= $user ? '/dashboard' : '/' ?>">
      <span class="brand-mark" aria-hidden="true">N</span>
      <span class="brand-text">
        <strong>NORTHSTAR</strong>
        <em>LOAD</em>
      </span>
    </a>
    <nav class="nav">
      <?php if ($user): ?>
        <a href="/dashboard">Dashboard</a>
        <a href="/projects">Projects</a>
        <a href="/media">Media</a>
        <a href="/downloads">Downloads</a>
        <a href="/plans">Plans</a>
        <a href="/docs">Docs</a>
        <a href="/account">Account</a>
        <?php if (\Northstar\Manage::isStaff($user)): ?>
          <a href="/manage/">Manage</a>
        <?php endif; ?>
        <a class="btn btn-ghost" href="/logout">Logout</a>
      <?php else: ?>
        <a href="/docs">Docs</a>
        <a href="/login">Sign in</a>
        <a class="btn btn-primary" href="/register">Start building</a>
      <?php endif; ?>
    </nav>
  </div>
</header>
<main class="site-main">
