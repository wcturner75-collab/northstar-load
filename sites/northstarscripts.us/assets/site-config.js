/**
 * Single place to edit public links for the apex site.
 * Paste your permanent Discord invite below, then re-upload this file.
 */
window.NorthstarSite = {
  discordInvite: 'https://discord.gg/PASTE_YOUR_INVITE',
  store: 'https://northstar-scripts.tebex.store/',
  load: 'https://load.northstarscripts.us/',
};
(function () {
  var cfg = window.NorthstarSite || {};
  var invite = cfg.discordInvite || '';
  if (!invite || invite.indexOf('PASTE_YOUR_INVITE') >= 0) return;
  document.querySelectorAll('[data-discord]').forEach(function (el) {
    el.setAttribute('href', invite);
    el.classList.remove('is-disabled');
  });
})();
