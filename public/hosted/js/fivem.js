/**
 * FiveM loading-screen event bridge.
 * Uses real game messages — never invents a fake % timer.
 *
 * Docs: https://docs.fivem.net/docs/scripting-manual/nui-development/loading-screens/
 */
(function () {
  function handle(data) {
    if (!data || typeof data !== 'object') return;
    if (!window.NSLoad) return;

    switch (data.eventName) {
      case 'loadProgress':
        if (Number.isFinite(data.loadFraction)) {
          window.NSLoad.setProgress(data.loadFraction);
        }
        break;
      case 'onLogLine':
        if (typeof data.message === 'string') {
          window.NSLoad.setStatus(data.message);
        }
        break;
      case 'startDataFileEntries':
        window.NSLoad.setStatus('Loading game files…');
        break;
      case 'onDataFileEntry':
        if (typeof data.name === 'string') {
          window.NSLoad.setStatus('Loading: ' + data.name.slice(0, 120));
        }
        break;
      case 'performMapLoadFunction':
        window.NSLoad.setStatus('Loading map…');
        break;
      case 'initFunctionInvoking':
        if (typeof data.name === 'string') {
          window.NSLoad.setStatus('Initializing: ' + data.name.slice(0, 120));
        }
        break;
      case 'startInitFunction':
      case 'startInitFunctionOrder':
        window.NSLoad.setStatus('Starting resources…');
        break;
      case 'endInitFunction':
      case 'endDataFileEntries':
        window.NSLoad.setStatus('Almost ready…');
        break;
      default:
        break;
    }
  }

  window.addEventListener('message', (event) => {
    handle(event.data);
  });
})();
