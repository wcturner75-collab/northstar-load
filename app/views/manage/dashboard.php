<section class="shell page-dash page-manage">
  <header class="page-head">
    <div>
      <p class="eyebrow">Management</p>
      <h1>Team console</h1>
      <p class="muted">Your role: <?= \Northstar\Security::e(strtoupper($role ?? 'manager')) ?></p>
    </div>
    <div class="row-actions">
      <a class="btn btn-ghost" href="/manage/users.php">Users</a>
      <a class="btn btn-ghost" href="/system-status.php">System status</a>
    </div>
  </header>

  <div class="stat-row">
    <div><span><?= (int) ($stats['users'] ?? 0) ?></span><label>Users</label></div>
    <div><span><?= (int) ($stats['projects'] ?? 0) ?></span><label>Projects</label></div>
    <div><span><?= (int) ($stats['builds'] ?? 0) ?></span><label>Ready builds</label></div>
  </div>
  <div class="stat-row">
    <div><span><?= (int) ($stats['media'] ?? 0) ?></span><label>Media files</label></div>
    <div><span><?= (int) ($stats['managers'] ?? 0) ?></span><label>Managers</label></div>
    <div><span>—</span><label>Ops</label></div>
  </div>

  <section class="doc-block">
    <h2>Quick links</h2>
    <p class="muted">Review accounts, disable abuse, and check schema health when something breaks.</p>
    <div class="row-actions" style="margin-top:0.75rem">
      <a class="btn btn-primary" href="/manage/users.php">Manage users</a>
      <a class="btn btn-ghost" href="/system-status.php">Database health</a>
    </div>
  </section>

  <?php if (($role ?? '') === 'admin'): ?>
  <section class="doc-block">
    <h2>Promote staff</h2>
    <p class="muted">Admins can change roles on the Users page. First admin (SQL once):</p>
    <pre class="mono" style="white-space:pre-wrap;font-size:0.85rem">UPDATE users SET role = 'admin' WHERE email = 'you@example.com';</pre>
  </section>
  <?php endif; ?>
</section>
