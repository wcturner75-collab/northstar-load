window.NSBuilder = window.NSBuilder || {};

NSBuilder.COMPONENTS = [
  { type: 'logo', label: 'Logo', w: 180, h: 180 },
  { type: 'serverName', label: 'Server Name', w: 900, h: 80 },
  { type: 'tagline', label: 'Tagline', w: 800, h: 48 },
  { type: 'text', label: 'Text', w: 400, h: 40 },
  { type: 'image', label: 'Image', w: 320, h: 180 },
  { type: 'loadingBar', label: 'Loading Bar', w: 700, h: 12 },
  { type: 'loadingStatus', label: 'Loading Status', w: 700, h: 32 },
  { type: 'musicPlayer', label: 'Music Player', w: 220, h: 48 },
  { type: 'rulesButton', label: 'Rules Button', w: 160, h: 44 },
  { type: 'discordButton', label: 'Discord Button', w: 160, h: 44 },
  { type: 'websiteButton', label: 'Website Button', w: 160, h: 44 },
  { type: 'socialButton', label: 'Social Button', w: 160, h: 44 },
  { type: 'serverInfo', label: 'Server Info', w: 360, h: 120 },
  { type: 'announcements', label: 'Announcements', w: 420, h: 220 },
  { type: 'staff', label: 'Staff', w: 480, h: 180 },
  { type: 'clock', label: 'Clock', w: 180, h: 48 },
  { type: 'panel', label: 'Panel', w: 400, h: 240 },
];

NSBuilder.uid = function () {
  return 'c_' + Math.random().toString(36).slice(2, 10);
};

NSBuilder.createComponent = function (type, x, y) {
  const meta = NSBuilder.COMPONENTS.find((c) => c.type === type) || { w: 200, h: 60, label: type };
  const base = {
    id: NSBuilder.uid(),
    type,
    name: meta.label,
    visible: true,
    locked: false,
    zIndex: 10,
    x: x ?? 200,
    y: y ?? 200,
    w: meta.w,
    h: meta.h,
    props: {},
  };

  switch (type) {
    case 'logo':
    case 'image':
      base.props = { mediaId: null, objectFit: 'contain', opacity: 1 };
      break;
    case 'serverName':
    case 'tagline':
    case 'text':
    case 'loadingStatus':
    case 'clock':
      base.props = {
        fontFamily: type === 'serverName' ? 'Orbitron' : 'Source Sans 3',
        fontSize: type === 'serverName' ? 56 : 20,
        fontWeight: type === 'serverName' ? 700 : 400,
        align: 'center',
        color: '#FFFFFF',
        text: type === 'text' ? 'Custom text' : '',
        shadow: type === 'serverName',
      };
      break;
    case 'loadingBar':
      base.props = { height: 12, radius: 2 };
      break;
    case 'musicPlayer':
      base.props = { label: 'Music' };
      break;
    case 'rulesButton':
    case 'discordButton':
    case 'websiteButton':
    case 'socialButton':
      base.props = { label: meta.label.replace(' Button', ''), url: 'https://example.com' };
      break;
    case 'panel':
      base.props = { background: 'rgba(0,0,0,0.45)', border: true };
      break;
    default:
      base.props = {};
  }
  return base;
};

NSBuilder.renderComponentContent = function (comp, doc) {
  const p = comp.props || {};
  const server = doc.server || {};
  const accent = (doc.theme && doc.theme.accent) || '#C4A35A';

  switch (comp.type) {
    case 'serverName':
      return textEl(server.name || 'Server', p);
    case 'tagline':
      return textEl(server.tagline || '', p);
    case 'text':
      return textEl(p.text || '', p);
    case 'loadingStatus':
      return textEl('Loading world…', Object.assign({}, p, { color: p.color || '#A8A8A8' }));
    case 'clock': {
      const d = new Date();
      return textEl(d.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' }), p);
    }
    case 'loadingBar': {
      const track = document.createElement('div');
      track.className = 'loading-bar-track';
      track.style.borderRadius = (p.radius || 0) + 'px';
      const fill = document.createElement('div');
      fill.className = 'loading-bar-fill indeterminate';
      fill.style.background = accent;
      track.appendChild(fill);
      return track;
    }
    case 'logo':
    case 'image': {
      const wrap = document.createElement('div');
      wrap.style.width = '100%';
      wrap.style.height = '100%';
      wrap.style.opacity = String(p.opacity ?? 1);
      wrap.style.background = 'rgba(255,255,255,0.06)';
      wrap.style.display = 'grid';
      wrap.style.placeItems = 'center';
      wrap.style.color = '#9a9aa2';
      wrap.textContent = p.mediaId ? ('Media #' + p.mediaId) : (comp.type === 'logo' ? 'Logo' : 'Image');
      if (p.mediaId) {
        const img = document.createElement('img');
        img.src = '/api/media/serve.php?id=' + encodeURIComponent(p.mediaId);
        img.alt = '';
        img.style.width = '100%';
        img.style.height = '100%';
        img.style.objectFit = p.objectFit || 'contain';
        wrap.textContent = '';
        wrap.appendChild(img);
      }
      return wrap;
    }
    case 'rulesButton':
    case 'discordButton':
    case 'websiteButton':
    case 'socialButton':
    case 'musicPlayer': {
      const btn = document.createElement('div');
      btn.textContent = p.label || comp.name;
      btn.style.width = '100%';
      btn.style.height = '100%';
      btn.style.display = 'grid';
      btn.style.placeItems = 'center';
      btn.style.border = '1px solid ' + accent;
      btn.style.color = '#fff';
      btn.style.fontFamily = 'Source Sans 3, sans-serif';
      btn.style.background = 'rgba(0,0,0,0.35)';
      return btn;
    }
    case 'announcements': {
      const box = document.createElement('div');
      box.style.padding = '12px';
      box.style.width = '100%';
      box.style.height = '100%';
      box.style.background = 'rgba(0,0,0,0.4)';
      box.style.overflow = 'hidden';
      const items = (doc.content && doc.content.announcements) || [];
      box.innerHTML = '';
      items.slice(0, 4).forEach((a) => {
        const t = document.createElement('div');
        t.style.marginBottom = '8px';
        t.innerHTML = '';
        const strong = document.createElement('strong');
        strong.textContent = a.title || '';
        strong.style.display = 'block';
        strong.style.color = accent;
        const body = document.createElement('span');
        body.textContent = a.body || '';
        body.style.color = '#ccc';
        body.style.fontSize = '14px';
        t.appendChild(strong);
        t.appendChild(body);
        box.appendChild(t);
      });
      if (!items.length) box.textContent = 'Announcements';
      return box;
    }
    case 'staff': {
      const box = document.createElement('div');
      box.style.padding = '12px';
      box.style.display = 'flex';
      box.style.gap = '12px';
      box.style.flexWrap = 'wrap';
      box.style.background = 'rgba(0,0,0,0.35)';
      box.style.width = '100%';
      box.style.height = '100%';
      const staff = (doc.content && doc.content.staff) || [];
      if (!staff.length) {
        box.textContent = 'Staff';
        return box;
      }
      staff.slice(0, 6).forEach((s) => {
        const card = document.createElement('div');
        card.style.minWidth = '90px';
        const name = document.createElement('div');
        name.textContent = s.name || '';
        name.style.fontWeight = '700';
        const role = document.createElement('div');
        role.textContent = s.role || '';
        role.style.color = accent;
        role.style.fontSize = '12px';
        card.appendChild(name);
        card.appendChild(role);
        box.appendChild(card);
      });
      return box;
    }
    case 'serverInfo': {
      const box = document.createElement('div');
      box.style.padding = '12px';
      box.style.background = 'rgba(0,0,0,0.4)';
      box.style.width = '100%';
      box.style.height = '100%';
      const title = document.createElement('div');
      title.textContent = server.name || 'Server';
      title.style.fontFamily = 'Orbitron, sans-serif';
      title.style.marginBottom = '6px';
      const tag = document.createElement('div');
      tag.textContent = server.tagline || '';
      tag.style.color = '#aaa';
      box.appendChild(title);
      box.appendChild(tag);
      return box;
    }
    case 'panel': {
      const box = document.createElement('div');
      box.style.width = '100%';
      box.style.height = '100%';
      box.style.background = p.background || 'rgba(0,0,0,0.45)';
      if (p.border) box.style.border = '1px solid rgba(255,255,255,0.12)';
      return box;
    }
    default: {
      const el = document.createElement('div');
      el.textContent = comp.type;
      return el;
    }
  }
};

function textEl(text, p) {
  const el = document.createElement('div');
  el.textContent = text;
  el.style.width = '100%';
  el.style.fontFamily = (p.fontFamily || 'Source Sans 3') + ', sans-serif';
  el.style.fontSize = (p.fontSize || 20) + 'px';
  el.style.fontWeight = String(p.fontWeight || 400);
  el.style.textAlign = p.align || 'left';
  el.style.color = p.color || '#fff';
  el.style.letterSpacing = (p.letterSpacing || 0) + 'px';
  if (p.shadow) el.style.textShadow = '0 2px 18px rgba(0,0,0,0.65)';
  return el;
}
