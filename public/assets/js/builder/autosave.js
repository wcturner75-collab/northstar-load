window.NSBuilder = window.NSBuilder || {};

NSBuilder.Autosave = (function () {
  let timer = null;
  let projectId = null;
  let statusEl = null;
  let pending = null;

  function init(options) {
    projectId = options.projectId;
    statusEl = options.statusEl;
  }

  function schedule(doc) {
    pending = doc;
    setStatus('Saving…', 'is-saving');
    clearTimeout(timer);
    timer = setTimeout(flush, 1000);
  }

  async function flush() {
    if (!pending) return;
    const doc = pending;
    pending = null;
    try {
      await NS.api('/api/projects/save', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ id: projectId, config: doc }),
      });
      setStatus('Saved', '');
    } catch (err) {
      setStatus('Error', 'is-error');
      console.error(err);
    }
  }

  function setStatus(text, cls) {
    if (!statusEl) return;
    statusEl.textContent = text;
    statusEl.className = 'builder-status ' + (cls || '');
  }

  return { init, schedule, flush };
})();
