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

    const channel = new BroadcastChannel('messages');
    channel.addEventListener('message', event => {
        Notif.snackbarClear();
        window.focus();

        console.log(event.data);

        switch (event.data.type) {
            case 'call':
                break;
            case 'call_reject':
                VisioUtils.cancelLobby(event.data.data.fullJid, event.data.data.callId);
                break;
            case 'chat':
                Search.chat(event.data.data.jid, event.data.data.muc);
                break;
            default:
                if (event.data.data.url) {
                    MovimUtils.reload(event.data.data.url);
                }
                break;
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
