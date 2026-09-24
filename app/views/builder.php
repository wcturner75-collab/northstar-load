<?php
/** Bare builder chrome — no site footer clutter */
/** @var array $project */
/** @var string $csrf */
/** @var array $config */
/** @var array $entitlements */
/** @var string $editorMode */
$cfgJson = json_encode($project['config'] ?? [], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
$mode = (($editorMode ?? 'simple') === 'advanced') ? 'advanced' : 'simple';
?><!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="csrf-token" content="<?= \Northstar\Security::e($csrf) ?>">
  <title>Editor — <?= \Northstar\Security::e($project['name'] ?? 'Project') ?></title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Bebas+Neue&family=DM+Sans:wght@400;600;700&family=Orbitron:wght@500;700&family=Syne:wght@600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="/assets/css/app.css">
  <link rel="stylesheet" href="/assets/css/builder.css">
</head>
<body class="builder-body mode-<?= \Northstar\Security::e($mode) ?>" data-project-id="<?= (int) $project['id'] ?>" data-editor-mode="<?= \Northstar\Security::e($mode) ?>">
<script type="application/json" id="project-boot"><?= $cfgJson ?: '{}' ?></script>
<script type="application/json" id="entitlements-boot"><?= json_encode($entitlements ?? ['plan' => 'free', 'features' => []], JSON_UNESCAPED_SLASHES) ?></script>

<div class="builder-app">
  <header class="builder-top">
    <a class="brand compact" href="/projects.php">
      <span class="brand-mark">NS</span>
      <span>LOAD</span>
    </a>
    <div class="builder-project">
      <strong id="project-title"><?= \Northstar\Security::e($project['name']) ?></strong>
      <span class="mono" id="project-resource"><?= \Northstar\Security::e($project['resource_name']) ?></span>
    </div>
    <div class="mode-toggle" role="group" aria-label="Editor mode">
      <button type="button" id="mode-simple" class="<?= $mode === 'simple' ? 'active' : '' ?>">Simple</button>
      <button type="button" id="mode-advanced" class="<?= $mode === 'advanced' ? 'active' : '' ?>">Advanced</button>
    </div>
    <div class="builder-status" id="save-status">Saved</div>
    <div class="builder-plan" id="plan-badge" title="Your plan limits">FREE</div>
    <div class="builder-actions">
      <button type="button" class="btn btn-primary" id="btn-generate">Generate Resource</button>
    </div>
  </header>

  <aside class="builder-simple" id="simple-panel">
    <header class="simple-head">
      <h2>Guided setup</h2>
      <p>Change the essentials. Switch to Advanced anytime for drag-and-drop control.</p>
    </header>
    <nav class="simple-steps" id="simple-steps">
      <button type="button" class="active" data-simple-step="brand">1. Brand</button>
      <button type="button" data-simple-step="look">2. Look</button>
      <button type="button" data-simple-step="music">3. Music</button>
      <button type="button" data-simple-step="extras">4. Extras</button>
    </nav>
    <div class="simple-step-pane active stack-form" data-simple-pane="brand" id="simple-brand"></div>
    <div class="simple-step-pane stack-form" data-simple-pane="look" id="simple-look"></div>
    <div class="simple-step-pane stack-form" data-simple-pane="music" id="simple-music"></div>
    <div class="simple-step-pane stack-form" data-simple-pane="extras" id="simple-extras"></div>
  </aside>

  <aside class="builder-left">
    <div class="panel-tabs">
      <button type="button" class="active" data-left-tab="elements">Elements</button>
      <button type="button" data-left-tab="content">Content</button>
      <button type="button" data-left-tab="layers">Layers</button>
    </div>
    <div class="left-pane active" data-pane="elements">
      <p class="pane-label">Add component</p>
      <div class="component-palette" id="component-palette"></div>
      <p class="pane-label">Background</p>
      <div class="stack-form compact" id="bg-form"></div>
      <p class="pane-label">Music</p>
      <div class="stack-form compact" id="music-form"></div>
    </div>
    <div class="left-pane" data-pane="content">
      <div class="stack-form compact" id="content-form"></div>
    </div>
    <div class="left-pane" data-pane="layers">
      <ul class="layer-list" id="layer-list"></ul>
    </div>
  </aside>

  <section class="builder-center">
    <div class="canvas-stage" id="canvas-stage">
      <div class="canvas-frame" id="canvas-frame">
        <div class="canvas" id="canvas"></div>
      </div>
    </div>
  </section>

  <aside class="builder-right">
    <h2>Properties</h2>
    <div id="inspector" class="stack-form compact">
      <p class="muted">Select a component</p>
    </div>
  </aside>

  <footer class="builder-bottom">
    <div class="zoom-controls">
      <button type="button" id="btn-zoom-out">−</button>
      <span id="zoom-label">100%</span>
      <button type="button" id="btn-zoom-in">+</button>
    </div>
    <div class="res-presets" id="res-presets">
      <button type="button" data-res="1920x1080" class="active">1920×1080</button>
      <button type="button" data-res="2560x1440">2560×1440</button>
      <button type="button" data-res="3440x1440">3440×1440</button>
      <button type="button" data-res="3840x2160">3840×2160</button>
    </div>
    <label class="check"><input type="checkbox" id="snap-toggle" checked> Snap</label>
    <div class="history-controls">
      <button type="button" id="btn-undo">Undo</button>
      <button type="button" id="btn-redo">Redo</button>
    </div>
  </footer>
</div>

<div class="modal hidden" id="generate-modal">
  <div class="modal-card">
    <h3>Generate resource</h3>
    <p id="generate-msg">Packaging hosted FiveM resource…</p>
    <div id="generate-result" class="hidden"></div>
    <button type="button" class="btn btn-ghost" id="generate-close">Close</button>
  </div>
</div>

<script src="/assets/js/app.js"></script>
<script src="/assets/js/builder/history.js"></script>
<script src="/assets/js/builder/components.js"></script>
<script src="/assets/js/builder/fields.js"></script>
<script src="/assets/js/builder/themes.js"></script>
<script src="/assets/js/builder/canvas.js"></script>
<script src="/assets/js/builder/inspector.js"></script>
<script src="/assets/js/builder/autosave.js"></script>
<script src="/assets/js/builder/simple.js"></script>
<script src="/assets/js/builder/editor.js"></script>
</body>
</html>
