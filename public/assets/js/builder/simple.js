window.NSBuilder = window.NSBuilder || {};

NSBuilder.Simple = (function () {
  let doc = null;
  let onChange = () => {};
  let features = {};
  const F = () => NSBuilder.Fields;

  function toast(msg, type) {
    if (window.NS && typeof NS.toast === 'function') NS.toast(msg, type);
  }

  function init(options) {
    doc = options.doc;
    onChange = options.onChange;
    features = options.features || {};
    bindSteps();
    render();
  }

  function setDoc(next, opts) {
    doc = next;
    if (opts && opts.soft) return;
    render();
  }

  function bindSteps() {
    document.querySelectorAll('[data-simple-step]').forEach((btn) => {
      btn.addEventListener('click', () => {
        document.querySelectorAll('[data-simple-step]').forEach((b) => b.classList.remove('active'));
        document.querySelectorAll('[data-simple-pane]').forEach((p) => p.classList.remove('active'));
        btn.classList.add('active');
        const pane = document.querySelector('[data-simple-pane="' + btn.dataset.simpleStep + '"]');
        if (pane) pane.classList.add('active');
      });
    });
  }

  /** Preview-only update — never rebuild this form (keeps focus). */
  function preview() {
    onChange(doc, false, { skipUi: true });
  }

  /** Structural change — rebuild form after select changes field set. */
  function rebuild() {
    onChange(doc, false, { rebuildUi: true, forceUi: true });
  }

  function renderThemePicker(parent) {
    const wrap = document.createElement('div');
    wrap.className = 'theme-picker';
    const title = document.createElement('p');
    title.className = 'pane-label';
    title.textContent = 'Layout theme';
    wrap.appendChild(title);
    F().hint(wrap, 'Pick a layout look. Updates colors and arrangement.');
    const current = (doc.theme && doc.theme.preset) || 'cinematic';
    (NSBuilder.LAYOUT_THEMES || []).forEach((theme) => {
      const btn = document.createElement('button');
      btn.type = 'button';
      btn.className = 'theme-chip' + (theme.id === current || (theme.aliases || []).indexOf(current) >= 0 ? ' active' : '');
      btn.style.setProperty('--accent', theme.accent);
      const strong = document.createElement('strong');
      strong.textContent = theme.name;
      const span = document.createElement('span');
      span.textContent = theme.blurb;
      const swatch = document.createElement('span');
      swatch.className = 'theme-swatch';
      swatch.style.background = theme.accent;
      btn.appendChild(strong);
      btn.appendChild(span);
      btn.appendChild(swatch);
      btn.addEventListener('click', () => {
        NSBuilder.applyLayoutTheme(doc, theme.id, { reposition: true });
        onChange(doc, true, { rebuildUi: true, forceUi: true });
        toast('Applied “' + theme.name + '” layout theme', 'success');
      });
      wrap.appendChild(btn);
    });
    parent.appendChild(wrap);
  }

  function render() {
    const brand = document.getElementById('simple-brand');
    const look = document.getElementById('simple-look');
    const music = document.getElementById('simple-music');
    const extras = document.getElementById('simple-extras');
    if (!brand || !F()) return;

    const activeStep = document.querySelector('[data-simple-step].active')?.dataset.simpleStep || 'brand';

    brand.innerHTML = '';
    look.innerHTML = '';
    music.innerHTML = '';
    extras.innerHTML = '';

    doc.server = doc.server || {};
    doc.theme = doc.theme || {};
    doc.background = doc.background || {};
    doc.music = doc.music || {};
    doc.content = doc.content || { rules: [], announcements: [], staff: [], socials: [] };
    doc.watermark = doc.watermark || {};

    F().text(brand, 'Server name', doc.server.name || '', (v) => {
      doc.server.name = v;
      preview();
    });
    F().text(brand, 'Tagline', doc.server.tagline || '', (v) => {
      doc.server.tagline = v;
      preview();
    });
    F().text(brand, 'Made By (watermark)', doc.server.creator || doc.watermark.madeBy || doc.server.name || '', (v) => {
      doc.server.creator = v;
      doc.watermark.madeBy = v;
      preview();
    }, { placeholder: 'Your studio or server name' });
    F().color(brand, 'Accent color', doc.theme.accent || '#C4A35A', (v) => {
      doc.theme.accent = v;
      preview();
    });

    renderThemePicker(look);

    const bgOpts = [
      { value: 'color', label: 'Solid color' },
      { value: 'image', label: 'Image' },
    ];
    if (features.slideshow_background) bgOpts.push({ value: 'slideshow', label: 'Slideshow' });
    if (features.video_background) bgOpts.push({ value: 'video', label: 'Video' });
    else bgOpts.push({ value: 'video', label: 'Video (Pro)', disabled: true });

    F().select(look, 'Background type', doc.background.type || 'color', bgOpts, (v) => {
      if (v === 'slideshow' && !features.slideshow_background) {
        toast('Slideshow needs Standard+ or is locked on your plan.', 'error');
        return;
      }
      if (v === 'video' && !features.video_background) {
        toast('Video backgrounds need Pro.', 'error');
        return;
      }
      doc.background.type = v;
      preview();
    });
    F().color(look, 'Background color', doc.background.color || '#0B0C10', (v) => {
      doc.background.color = v;
      preview();
    });
    F().media(look, 'Background media', doc.background.mediaIds || [], (ids) => {
      doc.background.mediaIds = ids || [];
      if (doc.background.mediaIds.length && doc.background.type === 'color') {
        doc.background.type = 'image';
      }
      preview();
    }, {
      kind: (doc.background.type === 'video') ? 'video' : 'image',
      multiple: true,
      title: 'Background media',
      hint: 'Pick images (or video for video backgrounds).',
    });

    if (NSBuilder.isDualPanelLayout && NSBuilder.isDualPanelLayout(doc)) {
      F().text(look, 'Map', doc.server.map || '', (v) => {
        doc.server.map = v;
        preview();
      }, { placeholder: 'e.g. RP_xxx' });
      F().number(look, 'Slots', doc.server.slots ?? 64, (v) => {
        doc.server.slots = Math.max(0, Math.round(v));
        preview();
      }, { min: 0, step: 1 });
      F().text(look, 'Game mode', doc.server.mode || '', (v) => {
        doc.server.mode = v;
        preview();
      }, { placeholder: 'e.g. DarkRP / Roleplay' });
    }

    F().bool(music, 'Enable music', !!doc.music.enabled, (v) => {
      doc.music.enabled = v;
      preview();
    });

    const sourceOpts = [{ value: 'file', label: 'Uploaded audio file' }];
    if (features.youtube_music) {
      sourceOpts.push({ value: 'youtube', label: 'YouTube (hidden embed)' });
    } else {
      sourceOpts.push({ value: 'youtube', label: 'YouTube (locked)', disabled: true });
    }
    F().select(music, 'Music source', doc.music.source || 'file', sourceOpts, (v) => {
      if (v === 'youtube' && !features.youtube_music) {
        toast('YouTube music is not on your plan.', 'error');
        return;
      }
      doc.music.source = v;
      rebuild();
    });

    if ((doc.music.source || 'file') === 'youtube') {
      F().hint(music, 'Plays as a hidden embed in-game (no visible YouTube player).');
      F().text(music, 'YouTube URL', doc.music.youtubeUrl || '', (v) => {
        doc.music.youtubeUrl = v;
        doc.music.source = 'youtube';
        doc.music.enabled = true;
        preview();
      }, { placeholder: 'https://www.youtube.com/watch?v=…' });
    } else {
      F().media(music, 'Audio file', doc.music.mediaId || null, (id) => {
        doc.music.mediaId = id || null;
        doc.music.source = 'file';
        if (doc.music.mediaId) doc.music.enabled = true;
        preview();
      }, {
        kind: 'audio',
        multiple: false,
        title: 'Choose audio',
        hint: 'Select an uploaded MP3/OGG from your media library.',
      });
    }
    F().number(music, 'Volume', doc.music.volume ?? 0.15, (v) => {
      doc.music.volume = Math.max(0, Math.min(1, v));
      preview();
    }, { min: 0, max: 1, step: 0.05 });

    F().text(extras, 'Rules (one per line: Title | Body)', rulesToText(doc.content.rules), (v) => {
      doc.content.rules = textToRules(v);
      preview();
    }, { multiline: true, rows: 5 });
    F().text(extras, 'Discord URL', (doc.content.socials || []).find((s) => s.type === 'discord')?.url || '', (v) => {
      upsertSocial('discord', 'Discord', v);
      preview();
    }, { placeholder: 'https://discord.gg/…' });
    F().text(extras, 'Website URL', (doc.content.socials || []).find((s) => s.type === 'website')?.url || '', (v) => {
      upsertSocial('website', 'Website', v);
      preview();
    }, { placeholder: 'https://…' });
    if (features.announcements) {
      F().text(extras, 'Announcements (Title | Body per line)', rulesToText(doc.content.announcements), (v) => {
        doc.content.announcements = textToRules(v);
        preview();
      }, { multiline: true, rows: 4 });
    }

    document.querySelectorAll('[data-simple-step]').forEach((b) => {
      b.classList.toggle('active', b.dataset.simpleStep === activeStep);
    });
    document.querySelectorAll('[data-simple-pane]').forEach((p) => {
      p.classList.toggle('active', p.dataset.simplePane === activeStep);
    });
  }

  function upsertSocial(type, label, url) {
    doc.content.socials = doc.content.socials || [];
    const idx = doc.content.socials.findIndex((s) => s.type === type);
    if (!url) {
      if (idx >= 0) doc.content.socials.splice(idx, 1);
      return;
    }
    const row = { type, label, url };
    if (idx >= 0) doc.content.socials[idx] = row;
    else doc.content.socials.push(row);
  }

  function rulesToText(rows) {
    return (rows || []).map((r) => ((r.title || '') + ' | ' + (r.body || '')).trim()).join('\n');
  }

  function textToRules(text) {
    return String(text || '').split('\n').map((line) => line.trim()).filter(Boolean).map((line) => {
      const parts = line.split('|');
      return {
        title: (parts[0] || '').trim(),
        body: (parts.slice(1).join('|') || '').trim(),
      };
    });
  }

  return { init, setDoc, render };
})();
