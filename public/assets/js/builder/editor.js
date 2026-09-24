(function () {
  const boot = document.getElementById('project-boot');
  let doc = JSON.parse(boot.textContent || '{}');
  if (!doc.version) {
    doc.version = 1;
  }
  const projectId = parseInt(document.body.dataset.projectId, 10);

  NSBuilder.History.push(doc);
  NSBuilder.Autosave.init({
    projectId,
    statusEl: document.getElementById('save-status'),
  });

  const canvasEl = document.getElementById('canvas');
  const stage = document.getElementById('canvas-stage');
  const frame = document.getElementById('canvas-frame');

  function commit(nextDoc, snapshot, meta) {
    if (meta && meta.action === 'delete') {
      nextDoc.components = nextDoc.components.filter((c) => c.id !== meta.id);
      nextDoc.layersOrder = (nextDoc.layersOrder || []).filter((id) => id !== meta.id);
      NSBuilder.Canvas.select(null);
    }
    if (meta && meta.action === 'duplicate') {
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
    NSBuilder.Canvas.setDoc(doc);
    NSBuilder.Inspector.setDoc(doc);
    NSBuilder.Inspector.render(NSBuilder.Canvas.getSelectedId());
    renderLayers();
    renderContentForms();
    if (snapshot) NSBuilder.History.push(doc);
    NSBuilder.Autosave.schedule(doc);
  }

  NSBuilder.Canvas.init({
    root: canvasEl,
    stage,
    frame,
    doc,
    snap: true,
    onChange: (d, snapshot) => commit(d, snapshot),
    onSelect: (id) => {
      NSBuilder.Inspector.render(id);
      renderLayers();
    },
  });

  NSBuilder.Inspector.init({
    host: document.getElementById('inspector'),
    doc,
    onChange: (d, snapshot, meta) => commit(d, snapshot, meta),
  });

  // Palette
  const palette = document.getElementById('component-palette');
  NSBuilder.COMPONENTS.forEach((c) => {
    const btn = document.createElement('button');
    btn.type = 'button';
    btn.textContent = c.label;
    btn.addEventListener('click', () => {
      const comp = NSBuilder.createComponent(c.type, 200, 200);
      doc.components = doc.components || [];
      doc.layersOrder = doc.layersOrder || [];
      doc.components.push(comp);
      doc.layersOrder.push(comp.id);
      commit(doc, true);
      NSBuilder.Canvas.select(comp.id);
    });
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
        commit(doc, true);
      };
      li.appendChild(name);
      li.appendChild(vis);
      li.addEventListener('click', () => NSBuilder.Canvas.select(comp.id));
      list.appendChild(li);
    });
  }

  function field(label, value, onInput) {
    const lab = document.createElement('label');
    lab.textContent = label;
    const input = document.createElement('input');
    input.value = value ?? '';
    input.addEventListener('input', () => onInput(input.value));
    lab.appendChild(input);
    return lab;
  }

  function renderContentForms() {
    const bg = document.getElementById('bg-form');
    const music = document.getElementById('music-form');
    const content = document.getElementById('content-form');
    bg.innerHTML = '';
    music.innerHTML = '';
    content.innerHTML = '';

    doc.background = doc.background || {};
    doc.music = doc.music || {};
    doc.server = doc.server || {};
    doc.theme = doc.theme || {};
    doc.content = doc.content || { rules: [], announcements: [], staff: [], socials: [] };

    bg.appendChild(field('Type (color|image|slideshow|video)', doc.background.type || 'color', (v) => {
      doc.background.type = v; commit(doc, false);
    }));
    bg.appendChild(field('Color', doc.background.color || '#0B0C10', (v) => {
      doc.background.color = v; commit(doc, false);
    }));
    bg.appendChild(field('Media IDs (comma)', (doc.background.mediaIds || []).join(','), (v) => {
      doc.background.mediaIds = v.split(',').map((s) => parseInt(s.trim(), 10)).filter(Boolean);
      commit(doc, false);
    }));

    music.appendChild(field('Enabled (true/false)', String(!!doc.music.enabled), (v) => {
      doc.music.enabled = v === 'true'; commit(doc, false);
    }));
    music.appendChild(field('Media ID', doc.music.mediaId || '', (v) => {
      doc.music.mediaId = parseInt(v, 10) || null; commit(doc, false);
    }));
    music.appendChild(field('Volume 0-1', doc.music.volume ?? 0.15, (v) => {
      doc.music.volume = Math.max(0, Math.min(1, parseFloat(v) || 0)); commit(doc, false);
    }));

    content.appendChild(field('Server name', doc.server.name || '', (v) => {
      doc.server.name = v; commit(doc, false);
    }));
    content.appendChild(field('Tagline', doc.server.tagline || '', (v) => {
      doc.server.tagline = v; commit(doc, false);
    }));
    content.appendChild(field('Accent', doc.theme.accent || '#C4A35A', (v) => {
      doc.theme.accent = v; commit(doc, false);
    }));
    content.appendChild(field('Rules JSON', JSON.stringify(doc.content.rules || []), (v) => {
      try { doc.content.rules = JSON.parse(v); commit(doc, false); } catch (_) {}
    }));
    content.appendChild(field('Announcements JSON', JSON.stringify(doc.content.announcements || []), (v) => {
      try { doc.content.announcements = JSON.parse(v); commit(doc, false); } catch (_) {}
    }));
    content.appendChild(field('Staff JSON', JSON.stringify(doc.content.staff || []), (v) => {
      try { doc.content.staff = JSON.parse(v); commit(doc, false); } catch (_) {}
    }));
    content.appendChild(field('Socials JSON', JSON.stringify(doc.content.socials || []), (v) => {
      try { doc.content.socials = JSON.parse(v); commit(doc, false); } catch (_) {}
    }));
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
      commit(doc, false);
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
      document.getElementById('generate-msg').textContent = 'Resource ready.';
      const result = document.getElementById('generate-result');
      result.classList.remove('hidden');
      result.innerHTML = '';
      const a = document.createElement('a');
      a.className = 'btn btn-primary';
      a.href = data.downloadUrl;
      a.textContent = 'Download ' + data.resourceName + '.zip';
      result.appendChild(a);
    } catch (err) {
      document.getElementById('generate-msg').textContent = err.message || 'Generate failed';
    }
  };
  document.getElementById('generate-close').onclick = () => modal.classList.add('hidden');
})();
