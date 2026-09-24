<section class="shell page-dash">
  <header class="page-head">
    <div>
      <p class="eyebrow">Northstar Load</p>
      <h1>Dashboard</h1>
      <p class="muted">Signed in as <?= \Northstar\Security::e($user['username'] ?? '') ?> · Plan <?= \Northstar\Security::e(strtoupper($plan ?? 'free')) ?></p>
    </div>
    <a class="btn btn-primary" href="/projects.php?new=1">New project</a>
  </header>

  <div class="stat-row">
    <div><span><?= (int) ($stats['projects'] ?? 0) ?></span><label>Projects</label></div>
    <div><span><?= (int) ($stats['media'] ?? 0) ?></span><label>Media files</label></div>
    <div><span><?= (int) ($stats['builds'] ?? 0) ?></span><label>Builds</label></div>
  </div>

  <div class="two-col">
    <section>
      <h2>Recent projects</h2>
      <?php if (empty($projects)): ?>
        <p class="muted">No projects yet. Create your first loading screen.</p>
      <?php else: ?>
        <ul class="list-plain">
          <?php foreach ($projects as $p): ?>
            <li>
              <a href="/builder.php?id=<?= (int) $p['id'] ?>">
                <strong><?= \Northstar\Security::e($p['name']) ?></strong>
                <span class="mono"><?= \Northstar\Security::e($p['resource_name']) ?></span>
              </a>
            </li>
          <?php endforeach; ?>
        </ul>
      <?php endif; ?>
    </section>
    <section>
      <h2>Recent builds</h2>
      <?php if (empty($builds)): ?>
        <p class="muted">Generated ZIPs will appear here.</p>
      <?php else: ?>
        <ul class="list-plain">
          <?php foreach ($builds as $b): ?>
            <li>
              <strong><?= \Northstar\Security::e($b['project_name']) ?></strong>
              <span class="muted"><?= \Northstar\Security::e($b['created_at']) ?></span>
              <?php if (($b['status'] ?? '') === 'ready'): ?>
                <a href="/download.php?build=<?= \Northstar\Security::e($b['build_token']) ?>">Download</a>
              <?php endif; ?>
            </li>
          <?php endforeach; ?>
        </ul>
      <?php endif; ?>
    </section>
  </div>
</section>
