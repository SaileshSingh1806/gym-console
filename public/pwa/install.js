(() => {
    const root = document.querySelector('[data-gdh-pwa]');
    if (!root) return;
    const install = root.querySelector('[data-install]');
    const status = root.querySelector('[data-status]');
    const update = root.querySelector('[data-update]');
    const connection = root.querySelector('[data-connection]');
    let promptEvent = null;
    let waitingWorker = null;
    const standalone = () => matchMedia('(display-mode: standalone)').matches || navigator.standalone === true;
    const showStatus = message => { status.textContent = message; status.hidden = !message; };
    const refresh = () => { install.hidden = standalone(); connection.hidden = navigator.onLine; };
    refresh();
    window.addEventListener('online', refresh);
    window.addEventListener('offline', refresh);
    window.addEventListener('beforeinstallprompt', event => {
        event.preventDefault(); promptEvent = event; refresh();
    });
    window.addEventListener('appinstalled', () => { promptEvent = null; install.hidden = true; showStatus('App installed.'); });
    install.addEventListener('click', async () => {
        if (promptEvent) {
            const event = promptEvent; promptEvent = null;
            try { await event.prompt(); await event.userChoice; } catch (_) { showStatus('Use your browser menu to install this app.'); }
        } else if (/iPhone|iPad|iPod/.test(navigator.userAgent) || (navigator.platform === 'MacIntel' && navigator.maxTouchPoints > 1)) {
            showStatus('In Safari, tap Share, then Add to Home Screen.');
        } else {
            showStatus('Install prompt is not ready. Open this page in Chrome or Edge, refresh once, and use Install app in the browser menu.');
        }
    });
    if (!('serviceWorker' in navigator) || !window.isSecureContext) return;
    const offerUpdate = worker => { waitingWorker = worker; update.hidden = false; };
    let reloadOnChange = false;
    navigator.serviceWorker.addEventListener('controllerchange', () => { if (reloadOnChange) location.reload(); });
    update.addEventListener('click', () => {
        if (!waitingWorker) return;
        reloadOnChange = true; waitingWorker.postMessage('SKIP_WAITING');
    });
    navigator.serviceWorker.register('/pwa-worker.js', {scope: '/app/', updateViaCache: 'none'}).then(reg => {
        if (reg.waiting) offerUpdate(reg.waiting);
        reg.addEventListener('updatefound', () => {
            const worker = reg.installing;
            if (worker) worker.addEventListener('statechange', () => {
                if (worker.state === 'installed' && navigator.serviceWorker.controller) offerUpdate(worker);
            });
        });
    }).catch(() => { showStatus('Installation support is unavailable in this browser.'); });
})();
