(function () {
  const state = {
    config: null,
    progressKnown: false,
    progress: 0,
    status: 'Connecting…',
    playerName: '',
  };

  async function loadConfig() {
    const fromWindow = (typeof window.NS_LOAD_CONFIG_URL === 'string' && window.NS_LOAD_CONFIG_URL)
      ? window.NS_LOAD_CONFIG_URL
      : '';
    const fromBody = document.body && document.body.dataset
      ? (document.body.dataset.configUrl || '')
      : '';
    const url = fromWindow || fromBody || '../config.json';
    const res = await fetch(url, { cache: 'no-store' });
    if (!res.ok) throw new Error('config missing');
    return res.json();
  }

  function pct(n, base) {
    return ((n / base) * 100) + '%';
  }

  function applyTheme(cfg) {
    const root = document.documentElement;
    const theme = cfg.theme || {};
    const colors = theme.colors || {};
    const fonts = theme.fonts || {};
    root.style.setProperty('--accent', theme.accent || '#C4A35A');
    root.style.setProperty('--text', colors.text || '#F5F5F5');
    root.style.setProperty('--muted', colors.muted || '#A8A8A8');
    root.style.setProperty('--panel', colors.panel || 'rgba(0,0,0,0.45)');
    root.style.setProperty('--font-display', '"' + (fonts.display || 'Syne') + '", sans-serif');
    root.style.setProperty('--font-body', '"' + (fonts.body || 'DM Sans') + '", sans-serif');
    const preset = theme.preset || 'cinematic';
    document.body.dataset.layout = preset;
    document.body.dataset.themeLayout = theme.layout || (preset === 'dual_panel' ? 'dual_panel' : 'freeform');
    document.body.style.fontFamily = 'var(--font-body)';
  }

  function isDualPanel(cfg) {
    const theme = cfg.theme || {};
    const preset = String(theme.preset || '').toLowerCase();
    const layout = String(theme.layout || '').toLowerCase();
    return layout === 'dual_panel' || layout === 'info_rules'
      || preset === 'dual_panel' || preset === 'info_rules' || preset === 'rulebook';
  }

  function madeByLabel(cfg) {
    const wm = cfg.watermark || {};
    const server = cfg.server || {};
    return (wm.madeBy && String(wm.madeBy).trim())
      || (server.creator && String(server.creator).trim())
      || server.name
      || 'Server';
  }

  function copyrightLabel(cfg) {
    const wm = cfg.watermark || {};
    return (wm.copyright && String(wm.copyright).trim()) || 'Northstar Load';
  }

  function renderWatermarks(cfg) {
    let host = document.getElementById('watermarks');
    if (!host) {
      host = document.createElement('div');
      host.id = 'watermarks';
      host.className = 'ns-watermarks';
      document.getElementById('stage')?.appendChild(host);
    }
    host.innerHTML = '';
    // Dual panel embeds credits inside the info card
    if (isDualPanel(cfg)) {
      host.style.display = 'none';
      return;
    }
    host.style.display = '';
    const made = document.createElement('div');
    made.className = 'ns-wm-made';
    made.textContent = 'Made By: ' + madeByLabel(cfg);
    const copy = document.createElement('div');
    copy.className = 'ns-wm-copy';
    copy.textContent = 'Copyright © ' + copyrightLabel(cfg);
    host.appendChild(made);
    host.appendChild(copy);
  }

  function setupBackground(cfg) {
    const el = document.getElementById('background');
    const ov = document.getElementById('overlay');
    const bg = cfg.background || {};
    const dual = isDualPanel(cfg);
    el.innerHTML = '';
    el.className = '';

    if (dual && (bg.type === 'color' || !(bg.assets && bg.assets.length))) {
      const base = bg.color || (cfg.theme && cfg.theme.accent) || '#B71C1C';
      el.style.background = 'radial-gradient(ellipse at center, ' + base + ' 0%, ' + base + ' 42%, #1a0505 100%)';
    } else if (bg.type === 'color' || !(bg.assets && bg.assets.length)) {
      el.style.background = bg.color || '#0B0C10';
    } else if (bg.type === 'video' && bg.assets[0]) {
      const video = document.createElement('video');
      video.src = bg.assets[0];
      video.autoplay = true;
      video.muted = true;
      video.loop = true;
      video.playsInline = true;
      video.style.width = '100%';
      video.style.height = '100%';
      video.style.objectFit = bg.fit || 'cover';
      el.appendChild(video);
    } else {
      el.classList.add('slideshow');
      if (bg.kenBurns) el.classList.add('kenburns');
      bg.assets.forEach((src, i) => {
        const img = document.createElement('img');
        img.src = src;
        if (i === 0) img.classList.add('active');
        el.appendChild(img);
      });
      if (bg.assets.length > 1) {
        let idx = 0;
        setInterval(() => {
          const imgs = el.querySelectorAll('img');
          imgs[idx].classList.remove('active');
          idx = (idx + 1) % imgs.length;
          imgs[idx].classList.add('active');
        }, bg.intervalMs || 8000);
      }
    }

    if (bg.overlay && bg.overlay.enabled && !dual) {
      ov.style.background = bg.overlay.color || '#000';
      ov.style.opacity = String(bg.overlay.opacity ?? 0.35);
      ov.style.display = 'block';
    } else {
      ov.style.display = 'none';
    }
  }

  function setupMusic(cfg) {
    const music = cfg.music || {};
    if (!music.enabled) return;

    const source = music.source || (music.youtubeId ? 'youtube' : 'file');
    if (source === 'youtube' && music.youtubeId) {
      setupYoutubeMusic(music);
      return;
    }
    if (!music.asset) return;

    const audio = document.getElementById('music');
    audio.src = music.asset;
    audio.loop = !!music.loop;
    audio.volume = Math.max(0, Math.min(1, music.volume ?? 0.15));

    const tryPlay = () => {
      audio.play().catch(() => showMusicHint(() => audio.play().catch(() => {})));
    };

    window.NSLoad._musicToggle = () => {
      if (audio.paused) audio.play().catch(() => {});
      else audio.pause();
    };

    if (music.autoplay) tryPlay();
    document.addEventListener('click', tryPlay, { once: true });
  }

  function showMusicHint(onClick) {
    const music = (state.config && state.config.music) || {};
    if (!music.startMutedHint) return;
    if (document.querySelector('.music-hint')) return;
    const hint = document.createElement('div');
    hint.className = 'music-hint';
    hint.textContent = 'Click to enable music';
    hint.addEventListener('click', () => {
      onClick();
      hint.remove();
    });
    document.body.appendChild(hint);
  }

  function setupYoutubeMusic(music) {
    const host = document.getElementById('yt-host');
    if (!host) return;
    const videoId = String(music.youtubeId || '').trim();
    if (!/^[a-zA-Z0-9_-]{11}$/.test(videoId)) return;

    let player = null;
    const vol = Math.round(Math.max(0, Math.min(1, music.volume ?? 0.15)) * 100);

    function mountPlayer() {
      player = new YT.Player('yt-player', {
        width: 1,
        height: 1,
        videoId: videoId,
        playerVars: {
          autoplay: music.autoplay ? 1 : 0,
          controls: 0,
          disablekb: 1,
          fs: 0,
          modestbranding: 1,
          playsinline: 1,
          rel: 0,
          loop: music.loop ? 1 : 0,
          playlist: music.loop ? videoId : undefined,
          origin: window.location.origin || undefined,
        },
        events: {
          onReady: (e) => {
            try {
              e.target.setVolume(vol);
              if (music.autoplay) e.target.playVideo();
            } catch (_) {}
          },
          onError: () => {
            state.status = 'Music unavailable';
            updateLoadingUI();
          },
        },
      });

      window.NSLoad._musicToggle = () => {
        if (!player || typeof player.getPlayerState !== 'function') return;
        const st = player.getPlayerState();
        if (st === YT.PlayerState.PLAYING) player.pauseVideo();
        else player.playVideo();
      };
    }

    window.onYouTubeIframeAPIReady = function () {
      mountPlayer();
    };

    if (window.YT && window.YT.Player) {
      mountPlayer();
    } else {
      const tag = document.createElement('script');
      tag.src = 'https://www.youtube.com/iframe_api';
      document.head.appendChild(tag);
    }

    // Browser/NUI autoplay policies may still require a gesture
    document.addEventListener('click', () => {
      if (player && typeof player.playVideo === 'function') {
        try {
          player.setVolume(vol);
          player.playVideo();
        } catch (_) {}
      }
    }, { once: true });

    if (music.startMutedHint) {
      showMusicHint(() => {
        if (player && player.playVideo) player.playVideo();
      });
    }
  }

  function textNode(text, props) {
    const el = document.createElement('div');
    el.className = 'ns-text';
    el.textContent = text;
    el.style.fontFamily = (props.fontFamily || 'Segoe UI') + ', sans-serif';
    el.style.fontSize = (props.fontSize || 20) + 'px';
    el.style.fontWeight = String(props.fontWeight || 400);
    el.style.textAlign = props.align || 'left';
    el.style.color = props.color || 'var(--text)';
    el.style.letterSpacing = (props.letterSpacing || 0) + 'px';
    if (props.shadow) el.style.textShadow = '0 2px 18px rgba(0,0,0,0.65)';
    return el;
  }

  function renderDualPanel(cfg) {
    const host = document.getElementById('components');
    host.innerHTML = '';
    const server = cfg.server || {};
    const content = cfg.content || {};
    const player = content.player || {};
    const accent = (cfg.theme && cfg.theme.accent) || '#C62828';
    const panelBg = (cfg.theme && cfg.theme.colors && cfg.theme.colors.panel) || 'rgba(40,0,0,0.55)';
    const rules = content.rules || [];

    const root = document.createElement('div');
    root.className = 'dual-panel-layout';
    root.style.setProperty('--dp-accent', accent);
    root.style.setProperty('--dp-panel', panelBg);

    const title = document.createElement('h1');
    title.className = 'dual-panel-title';
    title.textContent = server.name || 'Server';
    root.appendChild(title);

    const columns = document.createElement('div');
    columns.className = 'dual-panel-columns';

    const left = document.createElement('div');
    left.className = 'dual-panel-card';
    left.appendChild(dpHead('Server Info'));
    left.appendChild(dpRow('Name', server.name || '—', 'name'));
    left.appendChild(dpRow('Map', server.map || '—', 'map'));
    left.appendChild(dpRow('Slots', String(server.slots != null ? server.slots : '—'), 'slots'));
    left.appendChild(dpRow('Mode', server.mode || '—', 'mode'));
    const divider = document.createElement('div');
    divider.className = 'dual-panel-divider';
    left.appendChild(divider);
    left.appendChild(dpHead('Player Info'));
    left.appendChild(dpRow('Name', player.name || state.playerName || 'Connecting…', 'player'));
    left.appendChild(dpRow('SteamID', player.steamId || '—', 'steamid'));
    left.appendChild(dpRow('Last Seen', player.lastSeen || '—', 'seen'));

    const mark = document.createElement('div');
    mark.className = 'dual-panel-watermark';
    const line1 = document.createElement('div');
    line1.textContent = 'Made By: ' + madeByLabel(cfg);
    const line2 = document.createElement('div');
    line2.textContent = 'Copyright © ' + copyrightLabel(cfg);
    mark.appendChild(line1);
    mark.appendChild(line2);
    left.appendChild(mark);

    const right = document.createElement('div');
    right.className = 'dual-panel-card dual-panel-rules';
    right.appendChild(dpHead('Rules'));
    const list = document.createElement('ol');
    list.className = 'dual-panel-rules-list';
    const fallback = [
      { body: 'Respect staff and other players!' },
      { body: "Don't kill players without a reason (RDM)" },
      { body: "Don't kill players with cars (CDM)" },
    ];
    (rules.length ? rules : fallback).slice(0, 12).forEach((rule, i) => {
      const li = document.createElement('li');
      const num = String(i + 1).padStart(2, '0');
      const text = (rule.body && String(rule.body).trim())
        || (rule.title && String(rule.title).trim())
        || '';
      li.textContent = num + '. ' + text;
      list.appendChild(li);
    });
    right.appendChild(list);

    columns.appendChild(left);
    columns.appendChild(right);
    root.appendChild(columns);

    const foot = document.createElement('div');
    foot.className = 'dual-panel-loading';
    const track = document.createElement('div');
    track.className = 'ns-bar';
    const fill = document.createElement('div');
    fill.className = 'ns-bar-fill is-indeterminate';
    fill.dataset.role = 'bar';
    track.appendChild(fill);
    const status = document.createElement('div');
    status.className = 'dual-panel-status ns-text';
    status.dataset.role = 'status';
    status.textContent = state.status;
    foot.appendChild(track);
    foot.appendChild(status);
    root.appendChild(foot);

    host.appendChild(root);

    function dpHead(label) {
      const h = document.createElement('h2');
      h.className = 'dual-panel-section';
      h.textContent = label;
      return h;
    }
    function dpRow(label, value, kind) {
      const row = document.createElement('div');
      row.className = 'dual-panel-row';
      const icon = document.createElement('span');
      icon.className = 'dual-panel-icon dual-panel-icon-' + (kind || 'name');
      icon.setAttribute('aria-hidden', 'true');
      const text = document.createElement('div');
      text.className = 'dual-panel-row-text';
      const strong = document.createElement('strong');
      strong.textContent = label + ':';
      text.appendChild(strong);
      text.appendChild(document.createTextNode(' ' + value));
      row.appendChild(icon);
      row.appendChild(text);
      return row;
    }
  }

  function renderComponents(cfg) {
    if (isDualPanel(cfg)) {
      renderDualPanel(cfg);
      return;
    }

    const host = document.getElementById('components');
    host.innerHTML = '';
    const order = (cfg.layersOrder && cfg.layersOrder.length)
      ? cfg.layersOrder
      : (cfg.components || []).map((c) => c.id);

    order.forEach((id) => {
      const comp = (cfg.components || []).find((c) => c.id === id);
      if (!comp || comp.visible === false) return;
      const wrap = document.createElement('div');
      wrap.className = 'ns-comp';
      wrap.dataset.type = comp.type;
      wrap.dataset.id = comp.id;
      wrap.style.left = pct(comp.x, 1920);
      wrap.style.top = pct(comp.y, 1080);
      wrap.style.width = pct(comp.w, 1920);
      wrap.style.height = pct(comp.h, 1080);
      wrap.style.zIndex = String(comp.zIndex || 1);

      const p = comp.props || {};
      let child;
      switch (comp.type) {
        case 'serverName':
          child = textNode(cfg.server.name || '', p);
          break;
        case 'tagline':
          child = textNode(cfg.server.tagline || '', p);
          break;
        case 'text':
          child = textNode(p.text || '', p);
          break;
        case 'loadingStatus':
          child = textNode(state.status, p);
          child.dataset.role = 'status';
          break;
        case 'clock':
          child = textNode('', p);
          child.dataset.role = 'clock';
          break;
        case 'loadingBar': {
          child = document.createElement('div');
          child.className = 'ns-bar';
          child.style.borderRadius = (p.radius || 0) + 'px';
          const fill = document.createElement('div');
          fill.className = 'ns-bar-fill is-indeterminate';
          fill.dataset.role = 'bar';
          child.appendChild(fill);
          break;
        }
        case 'logo':
        case 'image': {
          child = document.createElement('img');
          child.src = p.asset || '';
          child.alt = '';
          child.style.width = '100%';
          child.style.height = '100%';
          child.style.objectFit = p.objectFit || 'contain';
          child.style.opacity = String(p.opacity ?? 1);
          break;
        }
        case 'rulesButton':
        case 'discordButton':
        case 'websiteButton':
        case 'socialButton': {
          child = document.createElement('a');
          child.className = 'ns-btn';
          child.textContent = p.label || comp.name;
          child.href = p.url || '#';
          child.target = '_blank';
          child.rel = 'noopener noreferrer';
          break;
        }
        case 'musicPlayer': {
          child = document.createElement('button');
          child.className = 'ns-btn';
          child.type = 'button';
          child.textContent = p.label || 'Music';
          child.addEventListener('click', () => {
            if (window.NSLoad && typeof window.NSLoad._musicToggle === 'function') {
              window.NSLoad._musicToggle();
              return;
            }
            const audio = document.getElementById('music');
            if (audio.paused) audio.play().catch(() => {});
            else audio.pause();
          });
          break;
        }
        case 'announcements': {
          child = document.createElement('div');
          child.className = 'ns-panel';
          (cfg.content.announcements || []).forEach((a) => {
            const h = document.createElement('h4');
            h.textContent = a.title || '';
            const para = document.createElement('p');
            para.textContent = a.body || '';
            child.appendChild(h);
            child.appendChild(para);
          });
          break;
        }
        case 'staff': {
          child = document.createElement('div');
          child.className = 'ns-panel';
          (cfg.content.staff || []).forEach((s) => {
            const h = document.createElement('h4');
            h.textContent = (s.name || '') + (s.role ? ' — ' + s.role : '');
            child.appendChild(h);
          });
          break;
        }
        case 'serverInfo': {
          child = document.createElement('div');
          child.className = 'ns-panel';
          const h = document.createElement('h4');
          h.textContent = cfg.server.name || '';
          const para = document.createElement('p');
          para.textContent = cfg.server.tagline || '';
          child.appendChild(h);
          child.appendChild(para);
          break;
        }
        case 'panel': {
          child = document.createElement('div');
          child.style.width = '100%';
          child.style.height = '100%';
          child.style.background = p.background || 'rgba(0,0,0,0.45)';
          if (p.border) child.style.border = '1px solid rgba(255,255,255,0.12)';
          break;
        }
        default:
          child = document.createElement('div');
      }
      wrap.appendChild(child);
      host.appendChild(wrap);
    });
  }

  function updateLoadingUI() {
    const cfg = state.config || {};
    const loading = cfg.loading || {};
    document.querySelectorAll('[data-role="status"]').forEach((el) => {
      if (loading.showStatus === false) {
        el.style.display = 'none';
        return;
      }
      let text = state.status;
      if (state.progressKnown && loading.showPercentWhenKnown !== false) {
        text = Math.round(state.progress * 100) + '% — ' + state.status;
      }
      el.textContent = text;
    });
    document.querySelectorAll('[data-role="bar"]').forEach((el) => {
      if (loading.showBar === false) {
        el.parentElement.style.display = 'none';
        return;
      }
      if (state.progressKnown) {
        el.classList.remove('is-indeterminate');
        el.style.width = Math.max(0, Math.min(100, state.progress * 100)) + '%';
      } else if (loading.indeterminateWhenUnknown !== false) {
        el.classList.add('is-indeterminate');
        el.style.width = '';
      }
    });
  }

  function tickClock() {
    const now = new Date();
    const text = now.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
    document.querySelectorAll('[data-role="clock"]').forEach((el) => {
      el.textContent = text;
    });
  }

  window.NSLoad = {
    setProgress(fraction) {
      if (typeof fraction === 'number' && Number.isFinite(fraction)) {
        state.progressKnown = true;
        state.progress = Math.max(0, Math.min(1, fraction));
        updateLoadingUI();
      }
    },
    setStatus(message) {
      if (typeof message === 'string' && message.trim()) {
        state.status = message.slice(0, 240);
        updateLoadingUI();
      }
    },
    markUnknown() {
      state.progressKnown = false;
      updateLoadingUI();
    },
  };

  async function boot() {
    try {
      const cfg = await loadConfig();
      state.config = cfg;
      applyTheme(cfg);
      setupBackground(cfg);
      setupMusic(cfg);
      renderComponents(cfg);
      renderWatermarks(cfg);
      updateLoadingUI();
      tickClock();
      setInterval(tickClock, 15000);

      if (window.nuiHandoverData && window.nuiHandoverData.name) {
        // Welcome hint only — never inject HTML
        const name = String(window.nuiHandoverData.name);
        state.playerName = name;
        state.status = 'Welcome, ' + name;
        if (cfg.content && cfg.content.player && !cfg.content.player.name) {
          cfg.content.player.name = name;
        }
        if (isDualPanel(cfg)) {
          renderComponents(cfg);
        }
        updateLoadingUI();
      }
    } catch (err) {
      console.error(err);
      state.status = 'Loading…';
      updateLoadingUI();
    }
  }

  boot();
})();
