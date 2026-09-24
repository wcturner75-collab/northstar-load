window.NSBuilder = window.NSBuilder || {};

/**
 * Unique layout themes — each has a different composition, chrome, and type voice.
 * dual_panel is a structured mode; others are freeform with CSS stage treatments.
 */
NSBuilder.LAYOUT_THEMES = [
  {
    id: 'cinematic',
    name: 'Spotlight',
    blurb: 'Centered marque under a soft stage glow.',
    accent: '#C4A35A',
    background: '#0A0B0F',
    overlay: 0.42,
    fonts: { display: 'Syne', body: 'DM Sans' },
    colors: { text: '#F4F0E6', muted: '#9C9588', panel: 'rgba(10,12,16,0.55)' },
    layout: {
      serverName: { x: 280, y: 360, w: 1360, h: 100 },
      tagline: { x: 420, y: 480, w: 1080, h: 44 },
      loadingBar: { x: 660, y: 900, w: 600, h: 6 },
      loadingStatus: { x: 660, y: 920, w: 600, h: 28 },
    },
  },
  {
    id: 'minimal',
    name: 'Quiet Line',
    blurb: 'Almost nothing — name + a hairline progress.',
    accent: '#ECEAE4',
    background: '#101218',
    overlay: 0.12,
    fonts: { display: 'DM Sans', body: 'DM Sans' },
    colors: { text: '#F2F1ED', muted: '#8E8E96', panel: 'rgba(16,18,24,0.35)' },
    layout: {
      serverName: { x: 96, y: 900, w: 820, h: 52 },
      tagline: { x: 96, y: 958, w: 640, h: 30 },
      loadingBar: { x: 96, y: 1016, w: 1728, h: 2 },
      loadingStatus: { x: 96, y: 1028, w: 480, h: 24 },
    },
  },
  {
    id: 'neon',
    name: 'Afterhours',
    blurb: 'Left rail brand with nightlife signal glow.',
    accent: '#2EE6A6',
    background: '#04070D',
    overlay: 0.48,
    fonts: { display: 'Orbitron', body: 'DM Sans' },
    colors: { text: '#E7FFF5', muted: '#6F9B8A', panel: 'rgba(4,14,18,0.62)' },
    layout: {
      serverName: { x: 120, y: 140, w: 980, h: 78 },
      tagline: { x: 120, y: 236, w: 720, h: 36 },
      loadingBar: { x: 120, y: 960, w: 520, h: 8 },
      loadingStatus: { x: 120, y: 982, w: 520, h: 28 },
    },
  },
  {
    id: 'dual_panel',
    aliases: ['info_rules', 'rulebook'],
    name: 'Rulebook',
    blurb: 'Server info + numbered rules on twin boards.',
    mode: 'dual_panel',
    accent: '#E11D48',
    background: '#7F1D1D',
    overlay: 0.2,
    fonts: { display: 'Syne', body: 'DM Sans' },
    colors: {
      text: '#FFFFFF',
      muted: 'rgba(255,255,255,0.78)',
      panel: 'rgba(24,6,10,0.62)',
    },
    layout: {
      serverName: { x: 260, y: 40, w: 1400, h: 72 },
      loadingBar: { x: 560, y: 1008, w: 800, h: 10 },
      loadingStatus: { x: 560, y: 1024, w: 800, h: 28 },
    },
  },
  {
    id: 'horizon',
    name: 'Coastline',
    blurb: 'Wide bottom HUD — brand sits on the shore line.',
    accent: '#5FB3D4',
    background: '#071018',
    overlay: 0.38,
    fonts: { display: 'Syne', body: 'DM Sans' },
    colors: { text: '#EAF4FA', muted: '#7E98A8', panel: 'rgba(6,16,24,0.7)' },
    layout: {
      serverName: { x: 72, y: 820, w: 980, h: 68 },
      tagline: { x: 72, y: 894, w: 760, h: 34 },
      loadingBar: { x: 72, y: 988, w: 1776, h: 5 },
      loadingStatus: { x: 72, y: 1008, w: 560, h: 26 },
    },
  },
  {
    id: 'ember',
    name: 'Foundry',
    blurb: 'Copper corner brackets — outlaw / industrial.',
    accent: '#E08A45',
    background: '#120C08',
    overlay: 0.4,
    fonts: { display: 'Bebas Neue', body: 'DM Sans' },
    colors: { text: '#F7EDE3', muted: '#B0896C', panel: 'rgba(28,14,8,0.58)' },
    layout: {
      serverName: { x: 360, y: 300, w: 1200, h: 96 },
      tagline: { x: 480, y: 410, w: 960, h: 40 },
      loadingBar: { x: 560, y: 910, w: 800, h: 12 },
      loadingStatus: { x: 560, y: 936, w: 800, h: 28 },
    },
  },
  {
    id: 'arctic',
    name: 'Overwatch',
    blurb: 'Cold steel masthead across the top edge.',
    accent: '#A9C6D6',
    background: '#0A1218',
    overlay: 0.3,
    fonts: { display: 'Syne', body: 'DM Sans' },
    colors: { text: '#F0F6FA', muted: '#849AAB', panel: 'rgba(10,18,26,0.72)' },
    layout: {
      serverName: { x: 96, y: 72, w: 1100, h: 60 },
      tagline: { x: 96, y: 142, w: 860, h: 32 },
      loadingBar: { x: 680, y: 980, w: 560, h: 4 },
      loadingStatus: { x: 680, y: 996, w: 560, h: 26 },
    },
  },
  {
    id: 'noir',
    name: 'Marquee',
    blurb: 'Letterboxed title card — high-contrast editorial.',
    accent: '#FFFFFF',
    background: '#000000',
    overlay: 0.08,
    fonts: { display: 'Bebas Neue', body: 'DM Sans' },
    colors: { text: '#FFFFFF', muted: '#8A8A8A', panel: 'rgba(0,0,0,0.7)' },
    layout: {
      serverName: { x: 160, y: 430, w: 1600, h: 110 },
      tagline: { x: 360, y: 560, w: 1200, h: 36 },
      loadingBar: { x: 160, y: 990, w: 1600, h: 2 },
      loadingStatus: { x: 160, y: 1004, w: 520, h: 24 },
    },
  },
  {
    id: 'stadium',
    name: 'Scoreboard',
    blurb: 'Chunky bottom plate — broadcast / match-day energy.',
    accent: '#F0C14A',
    background: '#0C0E12',
    overlay: 0.5,
    fonts: { display: 'Orbitron', body: 'DM Sans' },
    colors: { text: '#FFF6DF', muted: '#A89970', panel: 'rgba(8,10,14,0.82)' },
    layout: {
      serverName: { x: 96, y: 780, w: 1200, h: 64 },
      tagline: { x: 96, y: 852, w: 900, h: 32 },
      loadingBar: { x: 96, y: 940, w: 1728, h: 18 },
      loadingStatus: { x: 96, y: 972, w: 720, h: 28 },
    },
  },
  {
    id: 'glass',
    name: 'Glass Card',
    blurb: 'Frosted center card floating over the scene.',
    accent: '#8FD3C8',
    background: '#0B1420',
    overlay: 0.45,
    fonts: { display: 'Syne', body: 'DM Sans' },
    colors: { text: '#F3FAF8', muted: '#8AA8A2', panel: 'rgba(18,28,36,0.55)' },
    layout: {
      serverName: { x: 460, y: 390, w: 1000, h: 80 },
      tagline: { x: 520, y: 490, w: 880, h: 36 },
      loadingBar: { x: 560, y: 600, w: 800, h: 6 },
      loadingStatus: { x: 560, y: 622, w: 800, h: 28 },
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
    || preset === 'dual_panel' || preset === 'info_rules' || preset === 'rulebook';
};

/**
 * Apply a layout theme to the project doc (colors + optional component positions).
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
      if (comp.props) {
        if (theme.fonts && theme.fonts.display && ['serverName', 'tagline', 'text'].indexOf(comp.type) >= 0) {
          comp.props.fontFamily = theme.fonts.display;
        }
        if (theme.colors && theme.colors.text && comp.props.color) {
          if (comp.type === 'serverName' || comp.type === 'tagline') {
            comp.props.color = comp.type === 'tagline' ? theme.colors.muted : theme.colors.text;
          }
        }
        if (comp.type === 'serverName') {
          comp.props.align = theme.id === 'noir' || theme.id === 'cinematic' || theme.id === 'ember' || theme.id === 'glass'
            ? 'center'
            : (theme.id === 'minimal' || theme.id === 'neon' || theme.id === 'horizon' || theme.id === 'arctic' || theme.id === 'stadium'
              ? 'left'
              : (comp.props.align || 'center'));
        }
      }
    });
  }

  return doc;
};

/**
 * Build dual-panel DOM for canvas preview or shared structure.
 */
NSBuilder.renderDualPanel = function (doc, opts) {
  opts = opts || {};
  const server = doc.server || {};
  const content = doc.content || {};
  const player = content.player || {};
  const accent = (doc.theme && doc.theme.accent) || '#E11D48';
  const panelBg = (doc.theme && doc.theme.colors && doc.theme.colors.panel) || 'rgba(24,6,10,0.62)';
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
