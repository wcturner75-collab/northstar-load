window.NSBuilder = window.NSBuilder || {};

/**
 * Layout theme presets for loading screens.
 * Freeform themes reposition common components; dual_panel uses a structured layout mode.
 */
NSBuilder.LAYOUT_THEMES = [
  {
    id: 'cinematic',
    name: 'Cinematic',
    blurb: 'Centered gold branding — classic RP stage.',
    accent: '#C4A35A',
    background: '#0B0C10',
    overlay: 0.35,
    fonts: { display: 'Orbitron', body: 'Source Sans 3' },
    colors: { text: '#F5F5F5', muted: '#A8A8A8', panel: 'rgba(8,10,14,0.55)' },
    layout: {
      serverName: { x: 360, y: 340, w: 1200, h: 90 },
      tagline: { x: 460, y: 440, w: 1000, h: 48 },
      loadingBar: { x: 610, y: 920, w: 700, h: 12 },
      loadingStatus: { x: 610, y: 940, w: 700, h: 32 },
    },
  },
  {
    id: 'minimal',
    name: 'Minimal',
    blurb: 'Sparse and quiet — name + progress only.',
    accent: '#E8E6E1',
    background: '#12141A',
    overlay: 0.18,
    fonts: { display: 'Source Sans 3', body: 'Source Sans 3' },
    colors: { text: '#F2F2F0', muted: '#9A9AA2', panel: 'rgba(18,20,26,0.4)' },
    layout: {
      serverName: { x: 160, y: 880, w: 900, h: 64 },
      tagline: { x: 160, y: 950, w: 720, h: 36 },
      loadingBar: { x: 160, y: 1000, w: 1600, h: 4 },
      loadingStatus: { x: 160, y: 1012, w: 600, h: 28 },
    },
  },
  {
    id: 'neon',
    name: 'Neon Night',
    blurb: 'Teal glow on deep night for nightlife servers.',
    accent: '#3DDC97',
    background: '#05080F',
    overlay: 0.42,
    fonts: { display: 'Orbitron', body: 'Source Sans 3' },
    colors: { text: '#E8FFF6', muted: '#7AA898', panel: 'rgba(5,12,18,0.6)' },
    layout: {
      serverName: { x: 120, y: 160, w: 1100, h: 80 },
      tagline: { x: 120, y: 250, w: 800, h: 40 },
      loadingBar: { x: 120, y: 960, w: 640, h: 10 },
      loadingStatus: { x: 120, y: 980, w: 640, h: 28 },
    },
  },
  {
    id: 'dual_panel',
    aliases: ['info_rules'],
    name: 'Dual Panel',
    blurb: 'Info + Rules boards — translucent panels on a bold stage.',
    mode: 'dual_panel',
    accent: '#C62828',
    background: '#B71C1C',
    overlay: 0.22,
    fonts: { display: 'Source Sans 3', body: 'Source Sans 3' },
    colors: {
      text: '#FFFFFF',
      muted: 'rgba(255,255,255,0.78)',
      panel: 'rgba(40,0,0,0.55)',
    },
    // Freeform comps stay available in Advanced; dual_panel renderer owns the stage.
    layout: {
      serverName: { x: 260, y: 48, w: 1400, h: 72 },
      loadingBar: { x: 560, y: 1008, w: 800, h: 10 },
      loadingStatus: { x: 560, y: 1024, w: 800, h: 28 },
    },
  },
  {
    id: 'horizon',
    name: 'Horizon',
    blurb: 'Wide bottom HUD with left-aligned brand.',
    accent: '#7EB6D9',
    background: '#0A1218',
    overlay: 0.4,
    fonts: { display: 'Orbitron', body: 'Source Sans 3' },
    colors: { text: '#EEF5FA', muted: '#8AA0B0', panel: 'rgba(8,16,24,0.55)' },
    layout: {
      serverName: { x: 80, y: 780, w: 900, h: 72 },
      tagline: { x: 80, y: 860, w: 700, h: 40 },
      loadingBar: { x: 80, y: 980, w: 1760, h: 8 },
      loadingStatus: { x: 80, y: 1000, w: 500, h: 28 },
    },
  },
  {
    id: 'ember',
    name: 'Ember',
    blurb: 'Warm copper frame — outlaw / western energy.',
    accent: '#D4783A',
    background: '#140C08',
    overlay: 0.38,
    fonts: { display: 'Orbitron', body: 'Source Sans 3' },
    colors: { text: '#F6EDE4', muted: '#B0896C', panel: 'rgba(28,14,8,0.58)' },
    layout: {
      serverName: { x: 420, y: 280, w: 1080, h: 88 },
      tagline: { x: 520, y: 380, w: 880, h: 44 },
      loadingBar: { x: 560, y: 900, w: 800, h: 14 },
      loadingStatus: { x: 560, y: 926, w: 800, h: 30 },
    },
  },
  {
    id: 'arctic',
    name: 'Arctic',
    blurb: 'Cool steel header bar and crisp progress.',
    accent: '#A8C5D4',
    background: '#0B1218',
    overlay: 0.28,
    fonts: { display: 'Source Sans 3', body: 'Source Sans 3' },
    colors: { text: '#F0F6FA', muted: '#8FA6B4', panel: 'rgba(12,20,28,0.5)' },
    layout: {
      serverName: { x: 80, y: 60, w: 1000, h: 64 },
      tagline: { x: 80, y: 130, w: 800, h: 36 },
      loadingBar: { x: 640, y: 980, w: 640, h: 6 },
      loadingStatus: { x: 640, y: 996, w: 640, h: 28 },
    },
  },
  {
    id: 'noir',
    name: 'Noir',
    blurb: 'High-contrast editorial — monochrome stage.',
    accent: '#FFFFFF',
    background: '#000000',
    overlay: 0.15,
    fonts: { display: 'Orbitron', body: 'Source Sans 3' },
    colors: { text: '#FFFFFF', muted: '#8A8A8A', panel: 'rgba(0,0,0,0.65)' },
    layout: {
      serverName: { x: 200, y: 420, w: 1520, h: 100 },
      tagline: { x: 400, y: 540, w: 1120, h: 40 },
      loadingBar: { x: 200, y: 980, w: 1520, h: 3 },
      loadingStatus: { x: 200, y: 992, w: 600, h: 28 },
    },
  },
  {
    id: 'stadium',
    name: 'Stadium',
    blurb: 'Bold bottom scoreboard-style loading HUD.',
    accent: '#E2B84A',
    background: '#0E1014',
    overlay: 0.45,
    fonts: { display: 'Orbitron', body: 'Source Sans 3' },
    colors: { text: '#FFF8E8', muted: '#B0A488', panel: 'rgba(10,12,16,0.7)' },
    layout: {
      serverName: { x: 120, y: 820, w: 1100, h: 70 },
      tagline: { x: 120, y: 900, w: 800, h: 36 },
      loadingBar: { x: 120, y: 970, w: 1680, h: 16 },
      loadingStatus: { x: 120, y: 1000, w: 800, h: 30 },
    },
  },
];

NSBuilder.DEFAULT_DUAL_PANEL_RULES = [
  { title: 'Respect', body: 'Respect staff and other players!' },
  { title: 'No RDM', body: "Don't kill players without a reason (RDM)" },
  { title: 'No CDM', body: "Don't kill players with cars (CDM)" },
  { title: 'No FailRP', body: 'No FailRP' },
  { title: 'No Metagaming', body: 'No Metagaming' },
  { title: 'No Powergaming', body: 'No Powergaming' },
  { title: 'FearRP', body: 'FearRP' },
  { title: 'Fear Guns', body: 'Fear Guns' },
  { title: 'NLR', body: 'NLR' },
  { title: 'NLR Time', body: 'NLR Time - 5 Min.' },
];

NSBuilder.getTheme = function (id) {
  const key = String(id || 'cinematic').toLowerCase();
  return NSBuilder.LAYOUT_THEMES.find((t) =>
    t.id === key || (t.aliases && t.aliases.indexOf(key) >= 0)
  ) || NSBuilder.LAYOUT_THEMES[0];
};

NSBuilder.isDualPanelLayout = function (doc) {
  if (!doc || !doc.theme) return false;
  const preset = String(doc.theme.preset || '').toLowerCase();
  const layout = String(doc.theme.layout || '').toLowerCase();
  return layout === 'dual_panel' || layout === 'info_rules'
    || preset === 'dual_panel' || preset === 'info_rules';
};

/**
 * Apply a layout theme to the project doc (colors + optional component positions).
 * @param {object} doc
 * @param {string} themeId
 * @param {{ reposition?: boolean }} opts
 */
NSBuilder.applyLayoutTheme = function (doc, themeId, opts) {
  const theme = NSBuilder.getTheme(themeId);
  const reposition = !opts || opts.reposition !== false;

  doc.theme = doc.theme || {};
  doc.theme.preset = theme.id;
  doc.theme.layout = theme.mode === 'dual_panel' ? 'dual_panel' : 'freeform';
  doc.theme.accent = theme.accent;
  doc.theme.fonts = Object.assign({}, theme.fonts);
  doc.theme.colors = Object.assign({}, theme.colors);

  doc.background = doc.background || {};
  if (doc.background.type === 'color' || !doc.background.type) {
    doc.background.type = 'color';
    doc.background.color = theme.background;
  } else if (theme.mode === 'dual_panel' && doc.background.type === 'color') {
    doc.background.color = theme.background;
  }
  doc.background.overlay = doc.background.overlay || {};
  doc.background.overlay.enabled = true;
  doc.background.overlay.opacity = theme.overlay;
  if (theme.mode === 'dual_panel') {
    doc.background.overlay.color = '#000000';
  }

  doc.server = doc.server || {};
  if (theme.mode === 'dual_panel') {
    if (!doc.server.map) doc.server.map = 'RP_Map';
    if (doc.server.slots == null || doc.server.slots === '') doc.server.slots = 64;
    if (!doc.server.mode) doc.server.mode = 'Roleplay';
    doc.content = doc.content || {};
    if (!doc.content.rules || !doc.content.rules.length) {
      doc.content.rules = NSBuilder.DEFAULT_DUAL_PANEL_RULES.map((r) => Object.assign({}, r));
    }
    doc.content.player = doc.content.player || {
      name: 'Connecting…',
      steamId: 'STEAM_0:0:00000000',
      lastSeen: 'First join',
    };
  }

  doc.watermark = doc.watermark || {};
  const madeBy = doc.server.creator || doc.watermark.madeBy || doc.server.name || 'Server';
  doc.watermark.madeBy = madeBy;
  if (!doc.server.creator) doc.server.creator = madeBy;
  doc.watermark.copyright = 'NorthStar Scripts';

  if (reposition && theme.layout) {
    (doc.components || []).forEach((comp) => {
      const box = theme.layout[comp.type];
      if (!box) return;
      comp.x = box.x;
      comp.y = box.y;
      comp.w = box.w;
      comp.h = box.h;
    });
  }

  return doc;
};

/**
 * Build dual-panel DOM for canvas preview or shared structure.
 * @param {object} doc
 * @param {{ preview?: boolean }} opts
 */
NSBuilder.renderDualPanel = function (doc, opts) {
  opts = opts || {};
  const server = doc.server || {};
  const content = doc.content || {};
  const player = content.player || {};
  const accent = (doc.theme && doc.theme.accent) || '#C62828';
  const panelBg = (doc.theme && doc.theme.colors && doc.theme.colors.panel) || 'rgba(40,0,0,0.55)';
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

  left.appendChild(sectionHead('Server Info'));
  left.appendChild(infoRow('Name', server.name || '—', 'name'));
  left.appendChild(infoRow('Map', server.map || '—', 'map'));
  left.appendChild(infoRow('Slots', String(server.slots != null ? server.slots : '—'), 'slots'));
  left.appendChild(infoRow('Mode', server.mode || '—', 'mode'));

  const divider = document.createElement('div');
  divider.className = 'dual-panel-divider';
  left.appendChild(divider);

  left.appendChild(sectionHead('Player Info'));
  left.appendChild(infoRow('Name', player.name || 'Connecting…', 'player'));
  left.appendChild(infoRow('SteamID', player.steamId || '—', 'steamid'));
  left.appendChild(infoRow('Last Seen', player.lastSeen || '—', 'seen'));

  const mark = document.createElement('div');
  mark.className = 'dual-panel-watermark';
  const madeBy = (doc.watermark && doc.watermark.madeBy)
    || server.creator
    || server.name
    || 'Server';
  const copy = (doc.watermark && doc.watermark.copyright) || 'NorthStar Scripts';
  const line1 = document.createElement('div');
  line1.textContent = 'Made By: ' + madeBy;
  const line2 = document.createElement('div');
  line2.textContent = 'Copyright © ' + copy;
  mark.appendChild(line1);
  mark.appendChild(line2);
  left.appendChild(mark);

  const right = document.createElement('div');
  right.className = 'dual-panel-card dual-panel-rules';
  right.appendChild(sectionHead('Rules'));
  const list = document.createElement('ol');
  list.className = 'dual-panel-rules-list';
  const rows = rules.length ? rules : NSBuilder.DEFAULT_DUAL_PANEL_RULES;
  rows.slice(0, 12).forEach((rule, i) => {
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

  if (opts.preview !== false) {
    const foot = document.createElement('div');
    foot.className = 'dual-panel-loading';
    const track = document.createElement('div');
    track.className = 'loading-bar-track';
    const fill = document.createElement('div');
    fill.className = 'loading-bar-fill indeterminate';
    fill.style.background = accent;
    track.appendChild(fill);
    const status = document.createElement('div');
    status.className = 'dual-panel-status';
    status.textContent = 'Loading world…';
    foot.appendChild(track);
    foot.appendChild(status);
    root.appendChild(foot);
  }

  return root;

  function sectionHead(label) {
    const h = document.createElement('h2');
    h.className = 'dual-panel-section';
    h.textContent = label;
    return h;
  }

  function infoRow(label, value, kind) {
    const row = document.createElement('div');
    row.className = 'dual-panel-row';
    const icon = document.createElement('span');
    icon.className = 'dual-panel-icon dual-panel-icon-' + kind;
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
};
