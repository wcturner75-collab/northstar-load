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
