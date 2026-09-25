window.NSBuilder = window.NSBuilder || {};

NSBuilder.Canvas = (function () {
  let root, stage, frame, doc, selectedId = null, zoom = 1;
  let frameW = 1920, frameH = 1080;
  let onChange = () => {};
  let onSelect = () => {};
  let snap = true;
  const GRID = 8;

  function init(options) {
    root = options.root;
    stage = options.stage;
    frame = options.frame;
    doc = options.doc;
    onChange = options.onChange || onChange;
    onSelect = options.onSelect || onSelect;
    snap = options.snap !== false;
    fit();
    window.addEventListener('resize', fit);
    render();
  }

  function setDoc(next, opts) {
    doc = next;
    opts = opts || {};
    if (opts.debounce) {
      scheduleRender();
      return;
    }
    render();
  }

  let renderTimer = null;
  function scheduleRender() {
    if (renderTimer) cancelAnimationFrame(renderTimer);
    renderTimer = requestAnimationFrame(() => {
      renderTimer = null;
      render();
    });
  }

  function setSnap(v) { snap = !!v; }
  function getSelectedId() { return selectedId; }
  function select(id) {
    selectedId = id;
    render();
    onSelect(id);
  }

  function setResolution(w, h) {
    frameW = w;
    frameH = h;
    fit();
  }

  function setZoom(z) {
    zoom = Math.max(0.15, Math.min(1.5, z));
    fit();
  }

  function getZoom() { return zoom; }

  function fit() {
    const pad = 32;
    const availW = stage.clientWidth - pad;
    const availH = stage.clientHeight - pad;
    const baseScale = Math.min(availW / frameW, availH / frameH);
    const scale = baseScale * zoom;
    frame.style.width = frameW * scale + 'px';
    frame.style.height = frameH * scale + 'px';
    root.style.transform = 'scale(' + scale + ')';
    root.style.width = '1920px';
    root.style.height = '1080px';
  }

  function appendWatermarks() {
    if (root.querySelector('.canvas-watermarks')) return;
    const wm = document.createElement('div');
    wm.className = 'canvas-watermarks';
    const server = doc.server || {};
    const watermark = doc.watermark || {};
    const madeBy = (watermark.madeBy && String(watermark.madeBy).trim())
      || (server.creator && String(server.creator).trim())
      || server.name
      || 'Server';
    const copyName = (watermark.copyright && String(watermark.copyright).trim()) || 'Northstar Load';
    const made = document.createElement('div');
    made.className = 'canvas-wm-made';
    made.textContent = 'Made By: ' + madeBy;
    const copy = document.createElement('div');
    copy.className = 'canvas-wm-copy';
    copy.textContent = 'Copyright © ' + copyName;
    wm.appendChild(made);
    wm.appendChild(copy);
    root.appendChild(wm);
  }

  function render() {
    root.innerHTML = '';
    const preset = (doc.theme && doc.theme.preset) || 'cinematic';
    const theme = doc.theme || {};
    const colors = theme.colors || {};
    const fonts = theme.fonts || {};
    root.dataset.layout = preset;
    root.style.setProperty('--accent', theme.accent || '#C4A35A');
    root.style.setProperty('--text', colors.text || '#F5F5F5');
    root.style.setProperty('--muted', colors.muted || '#A8A8A8');
    root.style.setProperty('--panel', colors.panel || 'rgba(0,0,0,0.45)');
    root.style.setProperty('--font-display', '"' + (fonts.display || 'Syne') + '", sans-serif');
    root.style.setProperty('--font-body', '"' + (fonts.body || 'DM Sans') + '", sans-serif');

    const bg = document.createElement('div');
    bg.className = 'canvas-bg';
    const b = doc.background || {};
    const dual = typeof NSBuilder.isDualPanelLayout === 'function' && NSBuilder.isDualPanelLayout(doc);
    const accent = theme.accent || '#C4A35A';

    if (dual && (b.type === 'color' || !b.mediaIds || !b.mediaIds.length)) {
      const base = b.color || accent || '#7F1D1D';
      bg.style.background = 'radial-gradient(ellipse at center, ' + base + ' 0%, ' + base + ' 42%, #1a0505 100%)';
    } else if (b.type === 'color' || !b.mediaIds || !b.mediaIds.length) {
      bg.style.background = b.color || '#0B0C10';
    } else if (b.mediaIds && b.mediaIds[0]) {
      bg.style.backgroundImage = 'url(/api/media/serve.php?id=' + encodeURIComponent(b.mediaIds[0]) + ')';
    }
    root.appendChild(bg);

    // Stage chrome layer (unique per layout via CSS on #canvas[data-layout])
    const chrome = document.createElement('div');
    chrome.className = 'canvas-chrome';
    chrome.setAttribute('aria-hidden', 'true');
    root.appendChild(chrome);

    if (b.overlay && b.overlay.enabled && !dual) {
      const ov = document.createElement('div');
      ov.className = 'canvas-overlay';
      const op = b.overlay.opacity ?? 0.35;
      ov.style.background = b.overlay.color || '#000';
      ov.style.opacity = String(op);
      root.appendChild(ov);
    }

    if (dual && typeof NSBuilder.renderDualPanel === 'function') {
      const stage = NSBuilder.renderDualPanel(doc, { preview: true });
      stage.style.position = 'absolute';
      stage.style.inset = '0';
      stage.style.zIndex = '5';
      root.appendChild(stage);
      // Dual panel embeds its own Made By / Copyright block
      return;
    }

    const order = doc.layersOrder && doc.layersOrder.length
      ? doc.layersOrder
      : (doc.components || []).map((c) => c.id);

    order.forEach((id) => {
      const comp = (doc.components || []).find((c) => c.id === id);
      if (!comp) return;
      const el = document.createElement('div');
      el.className = 'comp' + (comp.id === selectedId ? ' selected' : '') + (comp.locked ? ' locked' : '') + (!comp.visible ? ' hidden-comp' : '');
      el.dataset.id = comp.id;
      el.style.left = comp.x + 'px';
      el.style.top = comp.y + 'px';
      el.style.width = comp.w + 'px';
      el.style.height = comp.h + 'px';
      el.style.zIndex = String(comp.zIndex || 1);
      el.appendChild(NSBuilder.renderComponentContent(comp, doc));

      if (comp.id === selectedId) {
        const handle = document.createElement('div');
        handle.className = 'handle';
        el.appendChild(handle);
        bindResize(el, handle, comp);
      }

      el.addEventListener('mousedown', (e) => {
        if (e.target.classList.contains('handle')) return;
        e.preventDefault();
        select(comp.id);
        if (comp.locked) return;
        beginDrag(e, comp);
      });

      root.appendChild(el);
    });

    appendWatermarks();
  }

  function beginDrag(e, comp) {
    const startX = e.clientX;
    const startY = e.clientY;
    const origX = comp.x;
    const origY = comp.y;
    const scale = getScale();

    function move(ev) {
      let nx = origX + (ev.clientX - startX) / scale;
      let ny = origY + (ev.clientY - startY) / scale;
      if (snap) {
        nx = Math.round(nx / GRID) * GRID;
        ny = Math.round(ny / GRID) * GRID;
      }
      comp.x = nx;
      comp.y = ny;
      render();
      showGuides(comp);
    }
    function up() {
      document.removeEventListener('mousemove', move);
      document.removeEventListener('mouseup', up);
      clearGuides();
      onChange(doc, true, { skipUi: true });
    }
    document.addEventListener('mousemove', move);
    document.addEventListener('mouseup', up);
  }

  function bindResize(el, handle, comp) {
    handle.addEventListener('mousedown', (e) => {
      e.preventDefault();
      e.stopPropagation();
      const startX = e.clientX;
      const startY = e.clientY;
      const origW = comp.w;
      const origH = comp.h;
      const scale = getScale();
      function move(ev) {
        let nw = Math.max(20, origW + (ev.clientX - startX) / scale);
        let nh = Math.max(20, origH + (ev.clientY - startY) / scale);
        if (snap) {
          nw = Math.round(nw / GRID) * GRID;
          nh = Math.round(nh / GRID) * GRID;
        }
        comp.w = nw;
        comp.h = nh;
        render();
      }
      function up() {
        document.removeEventListener('mousemove', move);
        document.removeEventListener('mouseup', up);
        onChange(doc, true, { skipUi: true });
      }
      document.addEventListener('mousemove', move);
      document.addEventListener('mouseup', up);
    });
  }

  function getScale() {
    const t = root.style.transform.match(/scale\(([^)]+)\)/);
    return t ? parseFloat(t[1]) : 1;
  }

  function showGuides(comp) {
    clearGuides();
    const cx = comp.x + comp.w / 2;
    const cy = comp.y + comp.h / 2;
    if (Math.abs(cx - 960) < 6) {
      const g = document.createElement('div');
      g.className = 'guide-v';
      g.style.left = '960px';
      root.appendChild(g);
      comp.x = 960 - comp.w / 2;
    }
    if (Math.abs(cy - 540) < 6) {
      const g = document.createElement('div');
      g.className = 'guide-h';
      g.style.top = '540px';
      root.appendChild(g);
      comp.y = 540 - comp.h / 2;
    }
  }

  function clearGuides() {
    root.querySelectorAll('.guide-v, .guide-h').forEach((n) => n.remove());
  }

  return { init, setDoc, select, getSelectedId, setResolution, setZoom, getZoom, setSnap, render, fit };
})();
