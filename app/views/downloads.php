<section class="shell page-dash">
  <header class="page-head">
    <div>
      <h1>Downloads</h1>
      <p class="muted">Generated resources. Download tokens are unique and ownership-checked.</p>
    </div>
  </header>

  <?php if (empty($builds)): ?>
    <p class="muted">No builds yet. Open a project and click Generate Resource.</p>
  <?php else: ?>
    <table>
      <thead>
        <tr><th>Project</th><th>Resource</th><th>Generated</th><th>Size</th><th>Status</th><th></th></tr>
      </thead>
      <tbody>
      <?php foreach ($builds as $b): ?>
        <tr>
          <td><?= \Northstar\Security::e($b['project_name']) ?></td>
          <td class="mono"><?= \Northstar\Security::e($b['resource_name']) ?></td>
          <td><?= \Northstar\Security::e($b['created_at']) ?></td>
          <td><?= number_format(((int)$b['file_size']) / 1024, 1) ?> KB</td>
          <td><?= \Northstar\Security::e($b['status']) ?></td>
          <td class="row-actions">
            <?php if ($b['status'] === 'ready'): ?>
              <a class="btn btn-small" href="/download.php?build=<?= \Northstar\Security::e($b['build_token']) ?>">Download</a>
            <?php endif; ?>
            <a class="btn btn-small" href="/builder.php?id=<?= (int) $b['project_id'] ?>">Regenerate</a>
            <button class="btn btn-small btn-danger" type="button" data-delete-build="<?= (int) $b['id'] ?>">Delete</button>
          </td>
        </tr>
      <?php endforeach; ?>
      </tbody>
    </table>
  <?php endif; ?>
</section>
<script>
document.querySelectorAll('[data-delete-build]').forEach(btn => {
  btn.addEventListener('click', async () => {
    if (!confirm('Delete this build ZIP?')) return;
    const csrf = document.querySelector('meta[name="csrf-token"]').content;
    const fd = new FormData();
    fd.append('id', btn.dataset.deleteBuild);
    fd.append('_csrf', csrf);
    const res = await fetch('/api/build/delete.php', { method: 'POST', headers: { 'X-CSRF-Token': csrf }, body: fd });
    const json = await res.json();
    if (!json.ok) { NS.toast(json.error?.message || 'Failed', 'error'); return; }
    location.reload();
  });
});
</script>
