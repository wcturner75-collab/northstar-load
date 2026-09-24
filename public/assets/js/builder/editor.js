(function () {
  const boot = document.getElementById('project-boot');
  let doc = JSON.parse(boot.textContent || '{}');
  if (!doc.version) {
    doc.version = 1;
  }
  const projectId = parseInt(document.body.dataset.projectId, 10);
  const entitlements = JSON.parse(document.getElementById('entitlements-boot')?.textContent || '{}');
  const features = entitlements.features || {};
  const maxComponents = entitlements.max_components || 12;
  const plan = (entitlements.plan || 'free').toUpperCase();
  const planBadge = document.getElementById('plan-badge');
  if (planBadge) planBadge.textContent = plan;

  let editorMode = document.body.dataset.editorMode === 'advanced' ? 'advanced' : 'simple';

  function toast(msg, type) {
    if (window.NS && typeof NS.toast === 'function') NS.toast(msg, type);
  }

  function canFeature(key) {
    return !!features[key];
  }

  function featureLockReason(key) {
    const labels = {
      youtube_music: 'YouTube music',
      slideshow_background: 'Slideshow backgrounds',
      video_background: 'Video backgrounds (Pro)',
      staff: 'Staff blocks',
      announcements: 'Announcements',
      ken_burns: 'Ken Burns (Standard+)',
    };
    return labels[key] || key;
  }

  function componentFeature(type) {
    if (type === 'staff') return 'staff';
    if (type === 'announcements') return 'announcements';
    return null;
  }

  /** True when the user is typing in a text field inside builder forms. */
  function isTextEditing() {
    const ae = document.activeElement;
    if (!ae) return false;
    const tag = ae.tagName;
    if (tag === 'TEXTAREA') return inBuilderForm(ae);
    if (tag !== 'INPUT') return false;
    const t = (ae.type || 'text').toLowerCase();
    if (['checkbox', 'radio', 'button', 'submit', 'file', 'color', 'range'].includes(t)) return false;
    return inBuilderForm(ae);
  }

  function inBuilderForm(el) {
    return !!(
      el.closest('#inspector') ||
      el.closest('#bg-form') ||
      el.closest('#music-form') ||
      el.closest('#content-form') ||
      el.closest('#simple-panel')
    );
  }

  /**
   * Form DOM rebuild steals focus. Only rebuild when explicitly requested
   * (rebuildUi / structural action). Default is NEVER rebuild — canvas only.
   */
  function shouldRebuildUi(meta) {
    meta = meta || {};
    if (meta.skipUi) return false;
    if (meta.action) return true;
    if (meta.rebuildUi) {
      if (meta.forceUi) return true;
      return !isTextEditing();
    }
    return false;
  }

  NSBuilder.History.push(doc);
  NSBuilder.Autosave.init({
    projectId,
    statusEl: document.getElementById('save-status'),
  });

  const canvasEl = document.getElementById('canvas');
  const stage = document.getElementById('canvas-stage');
  const frame = document.getElementById('canvas-frame');

  function commit(nextDoc, snapshot, meta) {
    meta = meta || {};
    if (meta.action === 'delete') {
      nextDoc.components = nextDoc.components.filter((c) => c.id !== meta.id);
      nextDoc.layersOrder = (nextDoc.layersOrder || []).filter((id) => id !== meta.id);
      NSBuilder.Canvas.select(null);
    }
    if (meta.action === 'duplicate') {
      const src = nextDoc.components.find((c) => c.id === meta.id);
      if (src) {
        const copy = JSON.parse(JSON.stringify(src));
        copy.id = NSBuilder.uid();
        copy.name = src.name + ' Copy';
        copy.x += 24;
        copy.y += 24;
        nextDoc.components.push(copy);
        nextDoc.layersOrder.push(copy.id);
        NSBuilder.Canvas.select(copy.id);
      }
    }

    doc = nextDoc;
    if (doc.server && doc.server.name) {
      document.getElementById('project-title').textContent = doc.server.name;
    }

    // Live preview: debounce while typing so focus stays in form fields
    const typing = isTextEditing() || !!meta.skipUi;
    NSBuilder.Canvas.setDoc(doc, typing ? { debounce: true } : {});
    NSBuilder.Inspector.setDoc(doc);

    const rebuild = shouldRebuildUi(meta);
    if (rebuild) {
      NSBuilder.Inspector.render(NSBuilder.Canvas.getSelectedId());
      renderLayers();
      renderContentForms();
      if (window.NSBuilder.Simple) NSBuilder.Simple.setDoc(doc);
    } else if (window.NSBuilder.Simple) {
      // Soft update: never rebuild Simple form DOM while typing
      NSBuilder.Simple.setDoc(doc, { soft: true });
    }

    if (snapshot) NSBuilder.History.push(doc);
    NSBuilder.Autosave.schedule(doc);
  }

  NSBuilder.Canvas.init({
    root: canvasEl,
    stage,
    frame,
    doc,
    snap: true,
    onChange: (d, snapshot) => commit(d, snapshot, { skipUi: true }),
    onSelect: (id) => {
      // Selection changes inspector — only if not mid-keystroke in a form
      if (!isTextEditing()) {
        NSBuilder.Inspector.render(id);
        renderLayers();
      }
    },
  });

  NSBuilder.Inspector.init({
    host: document.getElementById('inspector'),
    doc,
    onChange: (d, snapshot, meta) => commit(d, snapshot, meta || { skipUi: true }),
  });

  if (window.NSBuilder.Simple) {
    NSBuilder.Simple.init({
      doc,
      features,
      onChange: (d, snapshot, meta) => commit(d, snapshot, meta || { skipUi: true }),
    });
  }

  function applyEditorMode(mode) {
    editorMode = mode === 'advanced' ? 'advanced' : 'simple';
    document.body.classList.toggle('mode-simple', editorMode === 'simple');
    document.body.classList.toggle('mode-advanced', editorMode === 'advanced');
    document.body.dataset.editorMode = editorMode;
    document.getElementById('mode-simple')?.classList.toggle('active', editorMode === 'simple');
    document.getElementById('mode-advanced')?.classList.toggle('active', editorMode === 'advanced');
    NSBuilder.Canvas.fit();
    NS.api('/api/account/editor-mode.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ editor_mode: editorMode }),
    }).catch(() => {});
  }

  document.getElementById('mode-simple')?.addEventListener('click', () => applyEditorMode('simple'));
  document.getElementById('mode-advanced')?.addEventListener('click', () => applyEditorMode('advanced'));
  applyEditorMode(editorMode);

  // Palette
  const palette = document.getElementById('component-palette');
  NSBuilder.COMPONENTS.forEach((c) => {
    const btn = document.createElement('button');
    btn.type = 'button';
    const need = componentFeature(c.type);
    const locked = need && !canFeature(need);
    btn.textContent = locked ? c.label + ' 🔒' : c.label;
    if (locked) {
      btn.classList.add('palette-locked');
      btn.title = 'Upgrade required: ' + featureLockReason(need);
      btn.addEventListener('click', () => {
        toast(featureLockReason(need) + ' is not on your ' + plan + ' plan.', 'error');
      });
    } else {
      btn.addEventListener('click', () => {
        doc.components = doc.components || [];
        if (doc.components.length >= maxComponents) {
          toast('Your ' + plan + ' plan allows up to ' + maxComponents + ' components.', 'error');
          return;
        }
        const comp = NSBuilder.createComponent(c.type, 200, 200);
        doc.layersOrder = doc.layersOrder || [];
        doc.components.push(comp);
        doc.layersOrder.push(comp.id);
        commit(doc, true, { rebuildUi: true });
        NSBuilder.Canvas.select(comp.id);
      });
    }
    palette.appendChild(btn);
  });

  // Left tabs
  document.querySelectorAll('[data-left-tab]').forEach((btn) => {
    btn.addEventListener('click', () => {
      document.querySelectorAll('[data-left-tab]').forEach((b) => b.classList.remove('active'));
      document.querySelectorAll('.left-pane').forEach((p) => p.classList.remove('active'));
      btn.classList.add('active');
      document.querySelector('.left-pane[data-pane="' + btn.dataset.leftTab + '"]').classList.add('active');
    });
  });

  function renderLayers() {
    const list = document.getElementById('layer-list');
    list.innerHTML = '';
    const order = [...(doc.layersOrder || [])].reverse();
    order.forEach((id) => {
      const comp = (doc.components || []).find((c) => c.id === id);
      if (!comp) return;
      const li = document.createElement('li');
      if (comp.id === NSBuilder.Canvas.getSelectedId()) li.classList.add('active');
      const name = document.createElement('span');
      name.className = 'layer-name';
      name.textContent = comp.name;
      const vis = document.createElement('button');
      vis.type = 'button';
      vis.textContent = comp.visible ? '◉' : '◯';
      vis.title = 'Toggle visibility';
      vis.onclick = (e) => {
        e.stopPropagation();
        comp.visible = !comp.visible;
        commit(doc, true, { rebuildUi: true });
      };
      li.appendChild(name);
      li.appendChild(vis);
      li.addEventListener('click', () => NSBuilder.Canvas.select(comp.id));
      list.appendChild(li);
    });
  }

  function preview() {
    commit(doc, false, { skipUi: true });
  }

  function renderThemePicker(parent) {
    const wrap = document.createElement('div');
    wrap.className = 'theme-picker';
    const title = document.createElement('p');
    title.className = 'pane-label';
    title.textContent = 'Layout theme';
    wrap.appendChild(title);
    const current = (doc.theme && doc.theme.preset) || 'cinematic';
    (NSBuilder.LAYOUT_THEMES || []).forEach((theme) => {
      const btn = document.createElement('button');
      btn.type = 'button';
      btn.className = 'theme-chip' + (theme.id === current || (theme.aliases || []).indexOf(current) >= 0 ? ' active' : '');
      btn.dataset.themeId = theme.id;
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
        commit(doc, true, { rebuildUi: true, forceUi: true });
        toast('Applied “' + theme.name + '” layout theme', 'success');
      });
      wrap.appendChild(btn);
    });
    parent.appendChild(wrap);
  }

  function renderContentForms() {
    const bg = document.getElementById('bg-form');
    const music = document.getElementById('music-form');
    const content = document.getElementById('content-form');
    const F = NSBuilder.Fields;
    if (!bg || !F) return;
    bg.innerHTML = '';
    music.innerHTML = '';
    content.innerHTML = '';

    doc.background = doc.background || {};
    doc.music = doc.music || {};
    doc.server = doc.server || {};
    doc.theme = doc.theme || {};
    doc.content = doc.content || { rules: [], announcements: [], staff: [], socials: [] };
    doc.watermark = doc.watermark || {};

    renderThemePicker(bg);

    const bgOpts = [
      { value: 'color', label: 'Solid color' },
      { value: 'image', label: 'Image' },
      { value: 'slideshow', label: canFeature('slideshow_background') ? 'Slideshow' : 'Slideshow (locked)', disabled: !canFeature('slideshow_background') },
      { value: 'video', label: canFeature('video_background') ? 'Video' : 'Video (Pro)', disabled: !canFeature('video_background') },
    ];
    F.select(bg, 'Background type', doc.background.type || 'color', bgOpts, (v) => {
      if (v === 'slideshow' && !canFeature('slideshow_background')) {
        toast(featureLockReason('slideshow_background'), 'error');
        return;
      }
      if (v === 'video' && !canFeature('video_background')) {
        toast(featureLockReason('video_background'), 'error');
        return;
      }
      doc.background.type = v;
      preview();
    });
    F.color(bg, 'Background color', doc.background.color || '#0B0C10', (v) => {
      doc.background.color = v;
      preview();
    });
    F.text(bg, 'Media IDs', (doc.background.mediaIds || []).join(','), (v) => {
      doc.background.mediaIds = v.split(',').map((s) => parseInt(s.trim(), 10)).filter(Boolean);
      preview();
    }, { placeholder: 'e.g. 12, 15' });
    if (canFeature('ken_burns')) {
      F.bool(bg, 'Ken Burns', !!doc.background.kenBurns, (v) => {
        doc.background.kenBurns = v;
        preview();
      });
    } else {
      F.hint(bg, 'Ken Burns effect: Standard+ plan');
    }

    F.bool(music, 'Enable music', !!doc.music.enabled, (v) => {
      doc.music.enabled = v;
      preview();
    });
    const sourceOpts = [
      { value: 'file', label: 'Uploaded file' },
      { value: 'youtube', label: canFeature('youtube_music') ? 'YouTube (hidden)' : 'YouTube (locked)', disabled: !canFeature('youtube_music') },
    ];
    F.select(music, 'Music source', doc.music.source || 'file', sourceOpts, (v) => {
      if (v === 'youtube' && !canFeature('youtube_music')) {
        toast(featureLockReason('youtube_music'), 'error');
        return;
      }
      doc.music.source = v;
      if (v === 'youtube') doc.music.mediaId = null;
      else {
        doc.music.youtubeUrl = '';
        doc.music.youtubeId = null;
      }
      commit(doc, false, { rebuildUi: true, forceUi: true });
    });

    if ((doc.music.source || 'file') === 'youtube') {
      if (canFeature('youtube_music')) {
        F.hint(music, 'Hidden embed in the loading screen — no visible player.');
        F.text(music, 'YouTube URL', doc.music.youtubeUrl || '', (v) => {
          doc.music.youtubeUrl = v;
          doc.music.source = 'youtube';
          preview();
        }, { placeholder: 'https://www.youtube.com/watch?v=…' });
      }
    } else {
      F.number(music, 'Audio media ID', doc.music.mediaId || '', (v) => {
        doc.music.mediaId = v ? Math.round(v) : null;
        doc.music.source = 'file';
        preview();
      }, { min: 1, step: 1 });
    }
    if (!canFeature('youtube_music')) {
      F.hint(music, 'YouTube music embed: not on your plan');
    }
    F.number(music, 'Volume', doc.music.volume ?? 0.15, (v) => {
      doc.music.volume = Math.max(0, Math.min(1, v));
      preview();
    }, { min: 0, max: 1, step: 0.05 });

    F.text(content, 'Server name', doc.server.name || '', (v) => {
      doc.server.name = v;
      preview();
    });
    F.text(content, 'Tagline', doc.server.tagline || '', (v) => {
      doc.server.tagline = v;
      preview();
    });
    F.text(content, 'Made By (watermark)', doc.server.creator || doc.watermark.madeBy || '', (v) => {
      doc.server.creator = v;
      doc.watermark.madeBy = v;
      preview();
    });
    F.color(content, 'Accent', doc.theme.accent || '#C4A35A', (v) => {
      doc.theme.accent = v;
      preview();
    });
    if (NSBuilder.isDualPanelLayout && NSBuilder.isDualPanelLayout(doc)) {
      F.text(content, 'Map', doc.server.map || '', (v) => {
        doc.server.map = v;
        preview();
      });
      F.number(content, 'Slots', doc.server.slots ?? 64, (v) => {
        doc.server.slots = Math.max(0, Math.round(v));
        preview();
      }, { min: 0, step: 1 });
      F.text(content, 'Mode', doc.server.mode || '', (v) => {
        doc.server.mode = v;
        preview();
      });
    }
    F.text(content, 'Rules JSON', JSON.stringify(doc.content.rules || []), (v) => {
      try { doc.content.rules = JSON.parse(v); preview(); } catch (_) {}
    }, { multiline: true, rows: 3 });
    F.text(content, 'Announcements JSON', JSON.stringify(doc.content.announcements || []), (v) => {
      try { doc.content.announcements = JSON.parse(v); preview(); } catch (_) {}
    }, { multiline: true, rows: 3 });
    F.text(content, 'Staff JSON', JSON.stringify(doc.content.staff || []), (v) => {
      try { doc.content.staff = JSON.parse(v); preview(); } catch (_) {}
    }, { multiline: true, rows: 3 });
    F.text(content, 'Socials JSON', JSON.stringify(doc.content.socials || []), (v) => {
      try { doc.content.socials = JSON.parse(v); preview(); } catch (_) {}
    }, { multiline: true, rows: 3 });
  }

  renderLayers();
  renderContentForms();

  // Zoom / resolution
  document.getElementById('btn-zoom-in').onclick = () => {
    NSBuilder.Canvas.setZoom(NSBuilder.Canvas.getZoom() + 0.1);
    document.getElementById('zoom-label').textContent = Math.round(NSBuilder.Canvas.getZoom() * 100) + '%';
  };
  document.getElementById('btn-zoom-out').onclick = () => {
    NSBuilder.Canvas.setZoom(NSBuilder.Canvas.getZoom() - 0.1);
    document.getElementById('zoom-label').textContent = Math.round(NSBuilder.Canvas.getZoom() * 100) + '%';
  };
  document.getElementById('snap-toggle').onchange = (e) => NSBuilder.Canvas.setSnap(e.target.checked);

  document.querySelectorAll('#res-presets button').forEach((btn) => {
    btn.addEventListener('click', () => {
      document.querySelectorAll('#res-presets button').forEach((b) => b.classList.remove('active'));
      btn.classList.add('active');
      const [w, h] = btn.dataset.res.split('x').map(Number);
      doc.meta = doc.meta || {};
      doc.meta.previewMode = btn.dataset.res;
      NSBuilder.Canvas.setResolution(w, h);
      commit(doc, false, { skipUi: true });
    });
  });

  document.getElementById('btn-undo').onclick = () => {
    const prev = NSBuilder.History.undo(doc);
    if (!prev) return;
    doc = prev;
    NSBuilder.Canvas.setDoc(doc);
    NSBuilder.Inspector.setDoc(doc);
    NSBuilder.Inspector.render(NSBuilder.Canvas.getSelectedId());
    renderLayers();
    renderContentForms();
    if (window.NSBuilder.Simple) NSBuilder.Simple.setDoc(doc);
    NSBuilder.Autosave.schedule(doc);
  };
  document.getElementById('btn-redo').onclick = () => {
    const next = NSBuilder.History.redo();
    if (!next) return;
    doc = next;
    NSBuilder.Canvas.setDoc(doc);
    NSBuilder.Inspector.setDoc(doc);
    NSBuilder.Inspector.render(NSBuilder.Canvas.getSelectedId());
    renderLayers();
    renderContentForms();
    if (window.NSBuilder.Simple) NSBuilder.Simple.setDoc(doc);
    NSBuilder.Autosave.schedule(doc);
  };

  document.addEventListener('keydown', (e) => {
    if ((e.metaKey || e.ctrlKey) && e.key.toLowerCase() === 'z') {
      e.preventDefault();
      document.getElementById(e.shiftKey ? 'btn-redo' : 'btn-undo').click();
    }
    if (e.key === 'Delete' || e.key === 'Backspace') {
      const id = NSBuilder.Canvas.getSelectedId();
      if (!id) return;
      const tag = (e.target && e.target.tagName) || '';
      if (['INPUT', 'TEXTAREA', 'SELECT'].includes(tag)) return;
      commit(doc, true, { action: 'delete', id });
    }
  });

  // Generate
  const modal = document.getElementById('generate-modal');
  document.getElementById('btn-generate').onclick = async () => {
    modal.classList.remove('hidden');
    document.getElementById('generate-msg').textContent = 'Saving & packaging…';
    document.getElementById('generate-result').classList.add('hidden');
    try {
      await NSBuilder.Autosave.flush();
      await NS.api('/api/projects/save.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ id: projectId, config: doc }),
      });
      const data = await NS.api('/api/build/generate.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ project_id: projectId }),
      });
      document.getElementById('generate-msg').textContent = 'Resource ready (hosted).';
      const result = document.getElementById('generate-result');
      result.classList.remove('hidden');
      result.innerHTML = '';
      if (data.loadUrl) {
        const p = document.createElement('p');
        p.className = 'muted';
        p.textContent = 'Players load from:';
        result.appendChild(p);
        const link = document.createElement('a');
        link.href = data.loadUrl;
        link.target = '_blank';
        link.rel = 'noopener';
        link.textContent = data.loadUrl;
        result.appendChild(link);
      }
      const a = document.createElement('a');
      a.className = 'btn btn-primary';
      a.href = data.downloadUrl;
      a.style.marginTop = '0.75rem';
      a.style.display = 'inline-flex';
      a.textContent = 'Download ' + data.resourceName + '.zip';
      result.appendChild(a);
      toast('Hosted resource generated', 'success');
    } catch (err) {
      document.getElementById('generate-msg').textContent = err.message || 'Generate failed';
      toast(err.message || 'Generate failed', 'error');
    }
  };
  document.getElementById('generate-close').onclick = () => modal.classList.add('hidden');
})();
