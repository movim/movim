/**
 * Movim Base
 *
 * Some basic functions essential for Movim
 */

var onloaders = [];
var isTouch = false;

/**
 * @brief Adds a function to the onload event
 * @param function func
 */
function movimAddOnload(func) {
    if (typeof (func) === "function") {
        onloaders.push(func);
    }
}

/**
 * @brief Function that is run once the page is loaded.
 */
document.addEventListener("DOMContentLoaded", () => {
    movimLaunchOnload();
});

/**
 * @brief Execute onloader functions
 */
function movimLaunchOnload() {
    for (var i = 0; i < onloaders.length; i++) {
        onloaders[i]();
    }
}

window.addEventListener('touchstart', function () { isTouch = true; }, { once: true });

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


movimAddOnload(function () {
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
