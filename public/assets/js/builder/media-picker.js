window.NSBuilder = window.NSBuilder || {};

/**
 * Media library popup — pick image / audio / video without typing IDs.
 */
NSBuilder.MediaPicker = (function () {
  let open = false;
  let opts = null;

  function ensureModal() {
    let modal = document.getElementById('media-picker-modal');
    if (modal) return modal;
    modal = document.createElement('div');
    modal.id = 'media-picker-modal';
    modal.className = 'modal hidden media-picker-modal';
    modal.innerHTML = [
      '<div class="modal-card media-picker-card">',
      '  <header class="media-picker-head">',
      '    <h3 id="media-picker-title">Select media</h3>',
      '    <button type="button" class="btn btn-ghost btn-small" id="media-picker-close">Close</button>',
      '  </header>',
      '  <p class="muted" id="media-picker-hint"></p>',
      '  <div class="media-picker-toolbar">',
      '    <a class="btn btn-ghost btn-small" href="/media" target="_blank" rel="noopener">Open library</a>',
      '    <button type="button" class="btn btn-ghost btn-small" id="media-picker-refresh">Refresh</button>',
      '  </div>',
      '  <div class="media-picker-grid" id="media-picker-grid"></div>',
      '  <footer class="media-picker-foot">',
      '    <button type="button" class="btn btn-ghost" id="media-picker-clear">Clear</button>',
      '    <button type="button" class="btn btn-primary" id="media-picker-done">Use selected</button>',
      '  </footer>',
      '</div>',
    ].join('');
    document.body.appendChild(modal);
    modal.addEventListener('click', (e) => {
      if (e.target === modal) close();
    });
    document.getElementById('media-picker-close').onclick = close;
    document.getElementById('media-picker-refresh').onclick = () => load();
    document.getElementById('media-picker-clear').onclick = () => {
      if (!opts) return;
      opts.selected = opts.multiple ? [] : null;
      renderGrid([]);
      finish(true);
    };
    document.getElementById('media-picker-done').onclick = () => finish(false);
    return modal;
  }

  function openPicker(options) {
    opts = Object.assign({
      kind: 'image', // image | audio | video | null
      multiple: false,
      selected: null, // number | number[] | null
      title: 'Select media',
      hint: '',
      onPick: () => {},
    }, options || {});
    if (opts.multiple) {
      opts.selected = Array.isArray(opts.selected)
        ? opts.selected.map(Number).filter(Boolean)
        : (opts.selected ? [Number(opts.selected)] : []);
    } else {
      opts.selected = opts.selected ? Number(opts.selected) : null;
    }
    const modal = ensureModal();
    document.getElementById('media-picker-title').textContent = opts.title;
    document.getElementById('media-picker-hint').textContent = opts.hint
      || ('Showing your ' + (opts.kind || 'uploaded') + ' files. Click to select.');
    modal.classList.remove('hidden');
    open = true;
    load();
  }

  function close() {
    const modal = document.getElementById('media-picker-modal');
    if (modal) modal.classList.add('hidden');
    open = false;
    opts = null;
  }

  async function load() {
    const grid = document.getElementById('media-picker-grid');
    if (!grid || !opts) return;
    grid.innerHTML = '<p class="muted">Loading…</p>';
    try {
      const q = opts.kind ? ('?kind=' + encodeURIComponent(opts.kind)) : '';
      const data = await NS.api('/api/media/list' + q);
      renderGrid(data.media || []);
    } catch (err) {
      grid.innerHTML = '<p class="muted">' + (err.message || 'Could not load media') + '</p>';
    }
  }

  function isSelected(id) {
    if (!opts) return false;
    if (opts.multiple) return opts.selected.indexOf(id) >= 0;
    return opts.selected === id;
  }

  function toggle(id) {
    if (!opts) return;
    if (opts.multiple) {
      const i = opts.selected.indexOf(id);
      if (i >= 0) opts.selected.splice(i, 1);
      else opts.selected.push(id);
    } else {
      opts.selected = id;
    }
  }

  function renderGrid(items) {
    const grid = document.getElementById('media-picker-grid');
    if (!grid) return;
    grid.innerHTML = '';
    if (!items.length) {
      grid.innerHTML = '<p class="muted">No matching media yet. Upload files in the Media library first.</p>';
      return;
    }
    items.forEach((item) => {
      const id = Number(item.id);
      const card = document.createElement('button');
      card.type = 'button';
      card.className = 'media-picker-item' + (isSelected(id) ? ' is-selected' : '');
      card.dataset.id = String(id);

      const thumb = document.createElement('div');
      thumb.className = 'media-picker-thumb';
      if (item.kind === 'image') {
        const img = document.createElement('img');
        img.src = '/api/media/serve?id=' + encodeURIComponent(id);
        img.alt = '';
        thumb.appendChild(img);
      } else {
        thumb.textContent = item.kind === 'audio' ? '♪' : '▶';
      }

      const meta = document.createElement('div');
      meta.className = 'media-picker-meta';
      const name = document.createElement('strong');
      name.textContent = item.original_name || ('#' + id);
      const sub = document.createElement('span');
      sub.textContent = '#' + id + ' · ' + item.kind;
      meta.appendChild(name);
      meta.appendChild(sub);

      card.appendChild(thumb);
      card.appendChild(meta);
      card.addEventListener('click', () => {
        toggle(id);
        if (!opts.multiple) {
          finish(false);
          return;
        }
        card.classList.toggle('is-selected', isSelected(id));
      });
      grid.appendChild(card);
    });
  }

  function finish(cleared) {
    if (!opts) return;
    const cb = opts.onPick;
    const value = opts.multiple
      ? (cleared ? [] : opts.selected.slice())
      : (cleared ? null : opts.selected);
    close();
    cb(value);
  }

  return { open: openPicker, close };
})();
