window.NS = window.NS || {};

/** Account + Plans: editor mode + plan switching (external so CSP allows it). */
(function () {
  function csrf() {
    return document.querySelector('meta[name="csrf-token"]')?.content || '';
  }

  document.getElementById('editor-mode-form')?.addEventListener('submit', async (e) => {
    e.preventDefault();
    const status = document.getElementById('mode-save-status');
    const mode = e.target.editor_mode.value;
    if (status) status.textContent = 'Saving…';
    try {
      await NS.api('/api/account/editor-mode', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-Token': csrf() },
        body: JSON.stringify({ editor_mode: mode }),
      });
      if (status) status.textContent = 'Saved. New editor sessions use this mode.';
      NS.toast('Editor preference saved', 'success');
    } catch (err) {
      if (status) status.textContent = err.message || 'Save failed';
      NS.toast(err.message || 'Save failed', 'error');
    }
  });

  document.querySelectorAll('.btn-choose-plan').forEach((btn) => {
    btn.addEventListener('click', async () => {
      const plan = btn.dataset.plan;
      if (!plan) return;
      if (plan !== 'free') {
        NS.toast('Paid plans are not available yet — billing is not set up.', 'error');
        return;
      }
      btn.disabled = true;
      const label = btn.textContent;
      btn.textContent = 'Switching…';
      try {
        const data = await NS.api('/api/account/plan', {
          method: 'POST',
          headers: { 'Content-Type': 'application/json', 'X-CSRF-Token': csrf() },
          body: JSON.stringify({ plan }),
        });
        NS.toast('Plan updated to ' + String(data.plan || plan).toUpperCase(), 'success');
        location.reload();
      } catch (err) {
        btn.disabled = false;
        btn.textContent = label;
        NS.toast(err.message || 'Could not change plan', 'error');
      }
    });
  });
})();
