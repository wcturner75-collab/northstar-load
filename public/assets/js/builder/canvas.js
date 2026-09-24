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

  function setDoc(next) {
    doc = next;
    render();
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

  function render() {
    root.innerHTML = '';
    const bg = document.createElement('div');
    bg.className = 'canvas-bg';
    const b = doc.background || {};
    if (b.type === 'color' || !b.mediaIds || !b.mediaIds.length) {
      bg.style.background = b.color || '#0B0C10';
    } else if (b.mediaIds && b.mediaIds[0]) {
      bg.style.backgroundImage = 'url(/api/media/serve.php?id=' + encodeURIComponent(b.mediaIds[0]) + ')';
    }
    root.appendChild(bg);

    if (b.overlay && b.overlay.enabled) {
      const ov = document.createElement('div');
      ov.className = 'canvas-overlay';
      const op = b.overlay.opacity ?? 0.35;
      ov.style.background = b.overlay.color || '#000';
      ov.style.opacity = String(op);
      root.appendChild(ov);
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
      onChange(doc, true);
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
        onChange(doc, true);
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
