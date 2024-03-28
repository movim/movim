/**
 * Register a service worker for the Progressive Web App
 */
if ('serviceWorker' in navigator) {
    navigator.serviceWorker
        .register(BASE_URI + 'sw.js')
        .then(e => navigator.serviceWorker.ready)
        .then((r) => {
            r.active.postMessage({ base_uri: BASE_URI });
            console.log('Service Worker Registered');
        });
}

MovimEvents.registerWindow('loaded', 'movimbase', () => {
    const pwaButton = document.querySelector('#pwa');

    if (pwaButton) {
        window.addEventListener('beforeinstallprompt', (e) => {
            e.preventDefault();
            deferredPrompt = e;
            pwaButton.style.display = 'initial';

            pwaButton.addEventListener('click', () => {
                deferredPrompt.prompt();

                deferredPrompt.userChoice.then((choiceResult) => {
                    if (choiceResult.outcome === 'accepted') {
                        console.log('Movim App installed');
                    }

                    deferredPrompt = null;
                });
            });
        });
    }
});
