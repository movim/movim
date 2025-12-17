MovimWebsocket.attach(function() {
    if (Notification.permission !== 'granted') {
        NotificationConfig_ajaxHttpRequest();
    }

    navigator.serviceWorker.getRegistration(SW_URI).then((registration) => {
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
