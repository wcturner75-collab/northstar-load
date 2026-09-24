document.getElementById('btn-new-project')?.addEventListener('click', () => {
  location.href = '/projects.php?new=1';
});

document.getElementById('create-project-form')?.addEventListener('submit', async (e) => {
  e.preventDefault();
  const fd = new FormData(e.target);
  try {
    const res = await fetch('/api/projects/create.php', {
      method: 'POST',
      headers: { 'X-CSRF-Token': fd.get('_csrf'), 'Accept': 'application/json' },
      body: fd,
    });
    const json = await res.json();
    if (!json.ok) {
      NS.toast(json.error?.message || 'Create failed', 'error');
      return;
    }
    location.href = '/builder.php?id=' + json.data.project.id;
  } catch (err) {
    NS.toast(err.message || 'Create failed', 'error');
  }
});

document.querySelectorAll('[data-delete-project]').forEach((btn) => {
  btn.addEventListener('click', async () => {
    if (!confirm('Archive this project?')) return;
    const csrf = document.querySelector('meta[name="csrf-token"]').content;
    const fd = new FormData();
    fd.append('id', btn.dataset.deleteProject);
    fd.append('_csrf', csrf);
    try {
      const res = await fetch('/api/projects/delete.php', {
        method: 'POST',
        headers: { 'X-CSRF-Token': csrf },
        body: fd,
      });
      const json = await res.json();
      if (!json.ok) {
        NS.toast(json.error?.message || 'Delete failed', 'error');
        return;
      }
      location.reload();
    } catch (err) {
      NS.toast(err.message || 'Delete failed', 'error');
    }
  });
});
