document.getElementById('media-upload-form')?.addEventListener('submit', async (e) => {
  e.preventDefault();
  const status = document.getElementById('upload-status');
  const fd = new FormData(e.target);
  status.textContent = 'Uploading…';
  try {
    await NS.api('/api/media/upload.php', { method: 'POST', body: fd });
    status.textContent = 'Uploaded.';
    location.reload();
  } catch (err) {
    status.textContent = err.message;
  }
});

document.querySelectorAll('[data-delete-media]').forEach((btn) => {
  btn.addEventListener('click', async () => {
    if (!confirm('Delete this media file?')) return;
    const fd = new FormData();
    fd.append('id', btn.dataset.deleteMedia);
    fd.append('_csrf', NS.csrf());
    try {
      await NS.api('/api/media/delete.php', { method: 'POST', body: fd });
      location.reload();
    } catch (err) {
      NS.toast(err.message, 'error');
    }
  });
});
