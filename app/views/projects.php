<section class="shell page-dash">
  <header class="page-head">
    <div>
      <h1>Projects</h1>
      <p class="muted">Each project maps to one FiveM resource name.</p>
    </div>
    <button class="btn btn-primary" type="button" id="btn-new-project">New project</button>
  </header>

  <?php if (!empty($showCreate) || !empty($_GET['new'])): ?>
  <form class="create-panel stack-form" method="post" action="/api/projects/create.php" id="create-project-form">
    <input type="hidden" name="_csrf" value="<?= \Northstar\Security::e($csrf) ?>">
    <h2>Create project</h2>
    <label>Project name
      <input name="name" required maxlength="128" placeholder="Sierra Roleplay">
    </label>
    <label>Resource name
      <input name="resource_name" required pattern="[A-Za-z0-9_-]{1,64}" placeholder="sirp_loading">
    </label>
    <label>Template
      <select name="template_id">
        <option value="">Blank cinematic</option>
        <?php foreach ($templates as $t): ?>
          <option value="<?= (int) $t['id'] ?>"><?= \Northstar\Security::e($t['name']) ?></option>
        <?php endforeach; ?>
      </select>
    </label>
    <button class="btn btn-primary" type="submit">Create & open editor</button>
  </form>
  <?php endif; ?>

  <div class="project-table" id="project-list">
    <?php if (empty($projects)): ?>
      <p class="muted">No projects yet.</p>
    <?php else: ?>
      <table>
        <thead>
          <tr><th>Name</th><th>Resource</th><th>Theme</th><th>Updated</th><th></th></tr>
        </thead>
        <tbody>
        <?php foreach ($projects as $p): ?>
          <tr>
            <td><?= \Northstar\Security::e($p['name']) ?></td>
            <td class="mono"><?= \Northstar\Security::e($p['resource_name']) ?></td>
            <td><?= \Northstar\Security::e($p['theme_key']) ?></td>
            <td><?= \Northstar\Security::e($p['updated_at']) ?></td>
            <td class="row-actions">
              <a class="btn btn-small" href="/builder.php?id=<?= (int) $p['id'] ?>">Edit</a>
              <button class="btn btn-small btn-danger" data-delete-project="<?= (int) $p['id'] ?>" type="button">Delete</button>
            </td>
          </tr>
        <?php endforeach; ?>
        </tbody>
      </table>
    <?php endif; ?>
  </div>
</section>
<script>
document.getElementById('btn-new-project')?.addEventListener('click', () => {
  location.href = '/projects.php?new=1';
});
document.getElementById('create-project-form')?.addEventListener('submit', async (e) => {
  e.preventDefault();
  const fd = new FormData(e.target);
  const res = await fetch('/api/projects/create.php', {
    method: 'POST',
    headers: { 'X-CSRF-Token': fd.get('_csrf'), 'Accept': 'application/json' },
    body: fd
  });
  const json = await res.json();
  if (!json.ok) { NS.toast(json.error?.message || 'Create failed', 'error'); return; }
  location.href = '/builder.php?id=' + json.data.project.id;
});
document.querySelectorAll('[data-delete-project]').forEach(btn => {
  btn.addEventListener('click', async () => {
    if (!confirm('Archive this project?')) return;
    const csrf = document.querySelector('meta[name="csrf-token"]').content;
    const fd = new FormData();
    fd.append('id', btn.dataset.deleteProject);
    fd.append('_csrf', csrf);
    const res = await fetch('/api/projects/delete.php', { method: 'POST', headers: { 'X-CSRF-Token': csrf }, body: fd });
    const json = await res.json();
    if (!json.ok) { NS.toast(json.error?.message || 'Delete failed', 'error'); return; }
    location.reload();
  });
});
</script>
