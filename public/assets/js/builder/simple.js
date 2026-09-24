window.NSBuilder = window.NSBuilder || {};

NSBuilder.Simple = (function () {
  let doc = null;
  let onChange = () => {};
  let features = {};

  function init(options) {
    doc = options.doc;
    onChange = options.onChange;
    features = options.features || {};
    bindSteps();
    render();
  }

  function setDoc(next) {
    doc = next;
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

  function field(parent, label, value, onInput, type) {
    const lab = document.createElement('label');
    lab.textContent = label;
    const input = document.createElement(type === 'textarea' ? 'textarea' : 'input');
    if (type === 'textarea') {
      input.rows = 4;
    } else {
      input.type = type || 'text';
    }
    input.value = value ?? '';
    input.addEventListener('input', () => onInput(input.value));
    lab.appendChild(input);
    parent.appendChild(lab);
  }

  function render() {
    const brand = document.getElementById('simple-brand');
    const look = document.getElementById('simple-look');
    const music = document.getElementById('simple-music');
    const extras = document.getElementById('simple-extras');
    if (!brand) return;

    brand.innerHTML = '';
    look.innerHTML = '';
    music.innerHTML = '';
    extras.innerHTML = '';

    doc.server = doc.server || {};
    doc.theme = doc.theme || {};
    doc.background = doc.background || {};
    doc.music = doc.music || {};
    doc.content = doc.content || { rules: [], announcements: [], staff: [], socials: [] };

    field(brand, 'Server name', doc.server.name || '', (v) => {
      doc.server.name = v;
      // Keep serverName component text in sync via runtime config
      onChange(doc, false);
    });
    field(brand, 'Tagline', doc.server.tagline || '', (v) => {
      doc.server.tagline = v;
      onChange(doc, false);
    });
    field(brand, 'Accent color', doc.theme.accent || '#C4A35A', (v) => {
      doc.theme.accent = v;
      onChange(doc, false);
    }, 'color');

    const bgType = doc.background.type || 'color';
    field(look, 'Background type (color / image / slideshow)', bgType, (v) => {
      const allowed = ['color', 'image'];
      if (features.slideshow_background) allowed.push('slideshow');
      if (features.video_background) allowed.push('video');
      if (!allowed.includes(v)) {
        alert('That background type needs a higher plan or is invalid.');
        return;
      }
      doc.background.type = v;
      onChange(doc, false);
    });
    field(look, 'Background color', doc.background.color || '#0B0C10', (v) => {
      doc.background.color = v;
      onChange(doc, false);
    }, 'color');
    field(look, 'Background media IDs (from Media library)', (doc.background.mediaIds || []).join(', '), (v) => {
      doc.background.mediaIds = v.split(',').map((s) => parseInt(s.trim(), 10)).filter(Boolean);
      if (doc.background.mediaIds.length && doc.background.type === 'color') {
        doc.background.type = 'image';
      }
      onChange(doc, false);
    });

    field(music, 'Enable music (true/false)', String(!!doc.music.enabled), (v) => {
      doc.music.enabled = v === 'true';
      onChange(doc, false);
    });
    field(music, 'Source (file / youtube)', doc.music.source || 'file', (v) => {
      if (v === 'youtube' && !features.youtube_music) {
        alert('YouTube music is not on your plan.');
        return;
      }
      doc.music.source = v;
      onChange(doc, false);
    });
    if ((doc.music.source || 'file') === 'youtube') {
      const tip = document.createElement('p');
      tip.className = 'plan-hint';
      tip.textContent = 'Plays as a hidden embed in-game (no visible YouTube player).';
      music.appendChild(tip);
      field(music, 'YouTube URL', doc.music.youtubeUrl || '', (v) => {
        doc.music.youtubeUrl = v;
        doc.music.source = 'youtube';
        doc.music.enabled = true;
        onChange(doc, false);
      });
    } else {
      field(music, 'Audio media ID', doc.music.mediaId || '', (v) => {
        doc.music.mediaId = parseInt(v, 10) || null;
        doc.music.source = 'file';
        if (doc.music.mediaId) doc.music.enabled = true;
        onChange(doc, false);
      });
    }
    field(music, 'Volume (0–1)', doc.music.volume ?? 0.15, (v) => {
      doc.music.volume = Math.max(0, Math.min(1, parseFloat(v) || 0));
      onChange(doc, false);
    });

    field(extras, 'Rules (one per line: Title | Body)', rulesToText(doc.content.rules), (v) => {
      doc.content.rules = textToRules(v);
      onChange(doc, false);
    }, 'textarea');
    field(extras, 'Discord URL', (doc.content.socials || []).find((s) => s.type === 'discord')?.url || '', (v) => {
      upsertSocial('discord', 'Discord', v);
      onChange(doc, false);
    });
    field(extras, 'Website URL', (doc.content.socials || []).find((s) => s.type === 'website')?.url || '', (v) => {
      upsertSocial('website', 'Website', v);
      onChange(doc, false);
    });
    if (features.announcements) {
      field(extras, 'Announcements (Title | Body per line)', rulesToText(doc.content.announcements), (v) => {
        doc.content.announcements = textToRules(v);
        onChange(doc, false);
      }, 'textarea');
    }
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
