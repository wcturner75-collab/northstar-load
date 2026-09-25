/**
 * Single place to edit public links for the apex site.
 * Paste your permanent Discord invite below, then re-upload this file.
 */
window.NorthstarSite = {
  discordInvite: 'https://discord.gg/PASTE_YOUR_INVITE',
  store: 'https://northstar-scripts.tebex.store/',
  load: 'https://load.northstarscripts.us/',
  tawk: {
    enabled: true,
    embedSrc: 'https://embed.tawk.to/677746b949e2fd8dfe01db4e/1igkuogtd',
  },
};
(function () {
  var cfg = window.NorthstarSite || {};
  var invite = cfg.discordInvite || '';
  if (invite && invite.indexOf('PASTE_YOUR_INVITE') < 0) {
    document.querySelectorAll('[data-discord]').forEach(function (el) {
      el.setAttribute('href', invite);
      el.classList.remove('is-disabled');
    });
  }

  var tawk = cfg.tawk || {};
  if (!tawk.enabled || !tawk.embedSrc) return;
  if (window.Tawk_API) return;
  window.Tawk_API = window.Tawk_API || {};
  window.Tawk_LoadStart = new Date();
  var s1 = document.createElement('script');
  var s0 = document.getElementsByTagName('script')[0];
  s1.async = true;
  s1.src = tawk.embedSrc;
  s1.charset = 'UTF-8';
  s1.setAttribute('crossorigin', '*');
  s0.parentNode.insertBefore(s1, s0);
})();
