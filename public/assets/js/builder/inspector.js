window.NSBuilder = window.NSBuilder || {};

NSBuilder.Inspector = (function () {
  let host, doc, onChange;

  function init(options) {
    host = options.host;
    doc = options.doc;
    onChange = options.onChange;
  }

  function setDoc(next) { doc = next; }

  function touch(snapshot) {
    onChange(doc, snapshot, { skipUi: true });
  }

  function render(selectedId) {
    host.innerHTML = '';
    const comp = (doc.components || []).find((c) => c.id === selectedId);
    if (!comp) {
      host.innerHTML = '<p class="muted">Select a component</p>';
      return;
    }

    addText(host, 'Name', comp.name, (v) => { comp.name = v; touch(true); });
    addCheck(host, 'Visible', !!comp.visible, (v) => { comp.visible = v; touch(true); });
    addCheck(host, 'Locked', !!comp.locked, (v) => { comp.locked = v; touch(true); });
    addNumber(host, 'X', comp.x, (v) => { comp.x = v; touch(false); });
    addNumber(host, 'Y', comp.y, (v) => { comp.y = v; touch(false); });
    addNumber(host, 'Width', comp.w, (v) => { comp.w = Math.max(1, v); touch(false); });
    addNumber(host, 'Height', comp.h, (v) => { comp.h = Math.max(1, v); touch(false); });
    addNumber(host, 'Z Index', comp.zIndex || 1, (v) => { comp.zIndex = v; touch(true); });

    const p = comp.props || (comp.props = {});
    if ('fontSize' in p || ['serverName', 'tagline', 'text', 'loadingStatus', 'clock'].includes(comp.type)) {
      addText(host, 'Font', p.fontFamily || 'Source Sans 3', (v) => { p.fontFamily = v; touch(false); });
      addNumber(host, 'Size', p.fontSize || 20, (v) => { p.fontSize = v; touch(false); });
      addNumber(host, 'Weight', p.fontWeight || 400, (v) => { p.fontWeight = v; touch(false); });
      addSelect(host, 'Align', p.align || 'center', ['left', 'center', 'right'], (v) => { p.align = v; touch(true); });
      addText(host, 'Color', p.color || '#FFFFFF', (v) => { p.color = v; touch(false); });
    }
    if (comp.type === 'text') {
      addText(host, 'Text', p.text || '', (v) => { p.text = v; touch(false); });
    }
    if (comp.type === 'logo' || comp.type === 'image') {
      addNumber(host, 'Media ID', p.mediaId || 0, (v) => { p.mediaId = v || null; touch(true); });
      addSelect(host, 'Fit', p.objectFit || 'contain', ['contain', 'cover'], (v) => { p.objectFit = v; touch(true); });
    }
    if (['rulesButton', 'discordButton', 'websiteButton', 'socialButton'].includes(comp.type)) {
      addText(host, 'Label', p.label || '', (v) => { p.label = v; touch(false); });
      addText(host, 'URL', p.url || '', (v) => { p.url = v; touch(false); });
    }

    const actions = document.createElement('div');
    actions.style.display = 'flex';
    actions.style.gap = '0.35rem';
    actions.style.marginTop = '0.75rem';
    const dup = document.createElement('button');
    dup.type = 'button';
    dup.className = 'btn btn-small';
    dup.textContent = 'Duplicate';
    dup.onclick = () => onChange(doc, true, { action: 'duplicate', id: comp.id });
    const del = document.createElement('button');
    del.type = 'button';
    del.className = 'btn btn-small btn-danger';
    del.textContent = 'Delete';
    del.onclick = () => onChange(doc, true, { action: 'delete', id: comp.id });
    actions.appendChild(dup);
    actions.appendChild(del);
    host.appendChild(actions);
  }

  function addText(parent, label, value, cb) {
    const lab = document.createElement('label');
    lab.textContent = label;
    const input = document.createElement('input');
    input.type = 'text';
    input.value = value;
    input.addEventListener('input', () => cb(input.value));
    lab.appendChild(input);
    parent.appendChild(lab);
  }

  function addNumber(parent, label, value, cb) {
    const lab = document.createElement('label');
    lab.textContent = label;
    const input = document.createElement('input');
    input.type = 'number';
    input.value = value;
    input.addEventListener('input', () => cb(parseFloat(input.value) || 0));
    lab.appendChild(input);
    parent.appendChild(lab);
  }

  function addCheck(parent, label, value, cb) {
    const lab = document.createElement('label');
    lab.className = 'check';
    const input = document.createElement('input');
    input.type = 'checkbox';
    input.checked = value;
    input.addEventListener('change', () => cb(input.checked));
    lab.appendChild(input);
    lab.appendChild(document.createTextNode(' ' + label));
    parent.appendChild(lab);
  }

  function addSelect(parent, label, value, options, cb) {
    const lab = document.createElement('label');
    lab.textContent = label;
    const sel = document.createElement('select');
    options.forEach((o) => {
      const opt = document.createElement('option');
      opt.value = o;
      opt.textContent = o;
      if (o === value) opt.selected = true;
      sel.appendChild(opt);
    });
    sel.addEventListener('change', () => cb(sel.value));
    lab.appendChild(sel);
    parent.appendChild(lab);
  }

  return { init, setDoc, render };
})();
