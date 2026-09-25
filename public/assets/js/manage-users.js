document.querySelectorAll('[data-act]').forEach((btn) => {
  btn.addEventListener('click', async () => {
    const tr = btn.closest('tr');
    const userId = tr?.dataset.userId;
    if (!userId) return;
    btn.disabled = true;
    try {
      const body = { user_id: Number(userId), action: btn.dataset.act };
      if (btn.dataset.act === 'status') body.status = btn.dataset.status;
      if (btn.dataset.act === 'role') body.role = btn.dataset.role;
      await NS.api('/api/manage/user.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(body),
      });
      NS.toast('Updated', 'success');
      location.reload();
    } catch (err) {
      btn.disabled = false;
      NS.toast(err.message || 'Update failed', 'error');
    }
  });
});
