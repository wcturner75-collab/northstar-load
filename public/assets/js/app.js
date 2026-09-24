window.NS = window.NS || {};

NS.csrf = () => document.querySelector('meta[name="csrf-token"]')?.content || '';

NS.api = async function (url, options = {}) {
  const opts = { ...options };
  opts.headers = Object.assign({
    'Accept': 'application/json',
    'X-CSRF-Token': NS.csrf(),
  }, opts.headers || {});
  const res = await fetch(url, opts);
  const json = await res.json().catch(() => ({ ok: false, error: { message: 'Invalid response' } }));
  if (!res.ok || !json.ok) {
    const err = new Error(json.error?.message || 'Request failed');
    err.code = json.error?.code;
    throw err;
  }
  return json.data;
};

/** Non-blocking toast notifications (replaces browser alert()). */
NS.toast = function (message, type) {
  const text = String(message || '').trim() || 'Something went wrong';
  const kind = type === 'success' || type === 'info' ? type : 'error';
  let host = document.getElementById('ns-toast-host');
  if (!host) {
    host = document.createElement('div');
    host.id = 'ns-toast-host';
    host.className = 'ns-toast-host';
    host.setAttribute('aria-live', 'polite');
    host.setAttribute('aria-relevant', 'additions');
    document.body.appendChild(host);
  }
  const el = document.createElement('div');
  el.className = 'ns-toast ns-toast-' + kind;
  el.setAttribute('role', 'status');
  el.textContent = text;
  host.appendChild(el);
  requestAnimationFrame(() => el.classList.add('is-in'));
  const hideMs = kind === 'error' ? 5200 : 3600;
  setTimeout(() => {
    el.classList.remove('is-in');
    el.classList.add('is-out');
    setTimeout(() => el.remove(), 280);
  }, hideMs);
};
