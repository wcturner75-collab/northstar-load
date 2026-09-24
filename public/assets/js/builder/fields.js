window.NSBuilder = window.NSBuilder || {};

/**
 * Stable form field helpers for Simple + Advanced panels.
 * Prefer <select> for enums/booleans, <input type="color"> for colors,
 * text/number/textarea for free entry — never free-text for true/false.
 */
NSBuilder.Fields = (function () {
  function labelWrap(labelText) {
    const lab = document.createElement('label');
    lab.className = 'field-label';
    const span = document.createElement('span');
    span.className = 'field-caption';
    span.textContent = labelText;
    lab.appendChild(span);
    return lab;
  }

  function text(parent, label, value, onInput, opts) {
    opts = opts || {};
    const lab = labelWrap(label);
    const input = document.createElement(opts.multiline ? 'textarea' : 'input');
    if (opts.multiline) {
      input.rows = opts.rows || 4;
    } else {
      input.type = opts.type || 'text';
      if (opts.min != null) input.min = String(opts.min);
      if (opts.max != null) input.max = String(opts.max);
      if (opts.step != null) input.step = String(opts.step);
      if (opts.placeholder) input.placeholder = opts.placeholder;
    }
    input.value = value == null ? '' : String(value);
    input.autocomplete = 'off';
    input.addEventListener('input', () => onInput(input.value));
    lab.appendChild(input);
    parent.appendChild(lab);
    return input;
  }

  function number(parent, label, value, onInput, opts) {
    return text(parent, label, value, (v) => {
      const n = parseFloat(v);
      onInput(Number.isFinite(n) ? n : 0);
    }, Object.assign({ type: 'number', step: 'any' }, opts || {}));
  }

  function color(parent, label, value, onInput) {
    const lab = labelWrap(label);
    const row = document.createElement('div');
    row.className = 'color-field-row';
    const picker = document.createElement('input');
    picker.type = 'color';
    let hex = normalizeHex(value) || '#C4A35A';
    picker.value = hex;
    const hexInput = document.createElement('input');
    hexInput.type = 'text';
    hexInput.value = hex;
    hexInput.maxLength = 7;
    hexInput.spellcheck = false;
    hexInput.className = 'color-hex';
    picker.addEventListener('input', () => {
      hexInput.value = picker.value.toUpperCase();
      onInput(picker.value.toUpperCase());
    });
    hexInput.addEventListener('input', () => {
      const v = normalizeHex(hexInput.value);
      if (v) {
        picker.value = v;
        onInput(v);
      }
    });
    row.appendChild(picker);
    row.appendChild(hexInput);
    lab.appendChild(row);
    parent.appendChild(lab);
    return picker;
  }

  function select(parent, label, value, options, onChange) {
    const lab = labelWrap(label);
    const sel = document.createElement('select');
    options.forEach((opt) => {
      const o = document.createElement('option');
      if (typeof opt === 'string') {
        o.value = opt;
        o.textContent = opt;
      } else {
        o.value = opt.value;
        o.textContent = opt.label;
        if (opt.disabled) o.disabled = true;
      }
      if (String(o.value) === String(value)) o.selected = true;
      sel.appendChild(o);
    });
    sel.addEventListener('change', () => onChange(sel.value));
    lab.appendChild(sel);
    parent.appendChild(lab);
    return sel;
  }

  function bool(parent, label, value, onChange) {
    return select(parent, label, value ? 'true' : 'false', [
      { value: 'true', label: 'Yes' },
      { value: 'false', label: 'No' },
    ], (v) => onChange(v === 'true'));
  }

  function hint(parent, text) {
    const p = document.createElement('p');
    p.className = 'plan-hint';
    p.textContent = text;
    parent.appendChild(p);
    return p;
  }

  function normalizeHex(v) {
    const s = String(v || '').trim();
    if (/^#[0-9A-Fa-f]{6}$/.test(s)) return s.toUpperCase();
    if (/^[0-9A-Fa-f]{6}$/.test(s)) return ('#' + s).toUpperCase();
    return null;
  }

  return { text, number, color, select, bool, hint, normalizeHex };
})();
