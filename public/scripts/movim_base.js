/**
 * Register a service worker for the Progressive Web App
 */
if ('serviceWorker' in navigator) {
    navigator.serviceWorker
        .register(SW_URI)
        .then(e => navigator.serviceWorker.ready)
        .then((r) => {
            console.log('Service Worker Registered');
        });

    navigator.serviceWorker.addEventListener("message", (event) => {
        if (event.data.type == 'navigate') {
            MovimUtils.reload(event.data.url);
            window.focus();
        }
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
