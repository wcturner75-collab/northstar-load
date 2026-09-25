document.querySelectorAll('[data-delete-build]').forEach((btn) => {
  btn.addEventListener('click', async () => {
    if (!confirm('Delete this build ZIP?')) return;
    const csrf = document.querySelector('meta[name="csrf-token"]').content;
    const fd = new FormData();
    fd.append('id', btn.dataset.deleteBuild);
    fd.append('_csrf', csrf);
    try {
      const res = await fetch('/api/build/delete', {
        method: 'POST',
        headers: { 'X-CSRF-Token': csrf },
        body: fd,
      });
      const json = await res.json();
      if (!json.ok) {
        NS.toast(json.error?.message || 'Failed', 'error');
        return;
      }
      location.reload();
    } catch (err) {
      NS.toast(err.message || 'Failed', 'error');
    }
  });
});
