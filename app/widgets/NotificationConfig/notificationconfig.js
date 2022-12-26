MovimWebsocket.attach(function() {
    if (Notification.permission !== 'granted') {
        NotificationConfig_ajaxHttpRequest();
    }

    navigator.serviceWorker.getRegistration('sw.js').then((registration) => {
        if (!registration) {
            NotificationConfig_ajaxHttpPushGetConfig();
            return;
        }

        registration.pushManager.getSubscription().then((pushSubscription) => {
            NotificationConfig_ajaxHttpPushGetConfig(pushSubscription ? pushSubscription.endpoint : null);
        }, () => {
            NotificationConfig_ajaxHttpPushGetConfig();
        });
    });
});