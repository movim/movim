var Config = {
    switchNightMode: function()
    {
        document.body.classList.toggle('nightmode');
    }
}

MovimWebsocket.attach(function() {
    Config_ajaxMAMGetConfig();

    navigator.serviceWorker.getRegistration('sw.js').then((registration) => {
        if (!registration) {
            Config_ajaxHttpPushGetConfig();
            return;
        }

        registration.pushManager.getSubscription().then((pushSubscription) => {
            Config_ajaxHttpPushGetConfig(pushSubscription.endpoint);
        }, () => {
            Config_ajaxHttpPushGetConfig();
        });
    });
});
