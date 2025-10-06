self.addEventListener('message', (e) => {
    caches.open('movim').then((cache) => cache.addAll([
        // Audio
        e.data.base_uri + '/theme/audio/call.opus',
        e.data.base_uri + '/theme/audio/message.ogg',

        // Fonts
        e.data.base_uri + '/theme/fonts/MaterialIcons/font.css',
        e.data.base_uri + '/theme/fonts/MaterialIcons/MaterialIcons-Regular.woff2',
        e.data.base_uri + '/theme/fonts/Roboto/font.css',
        e.data.base_uri + '/theme/fonts/Roboto/KFOlCnqEu92Fr1MmSU5fCRc4EsA.woff2',
        e.data.base_uri + '/theme/fonts/Roboto/KFOlCnqEu92Fr1MmSU5fABc4EsA.woff2',
        e.data.base_uri + '/theme/fonts/Roboto/KFOlCnqEu92Fr1MmSU5fCBc4EsA.woff2',
        e.data.base_uri + '/theme/fonts/Roboto/KFOlCnqEu92Fr1MmSU5fBxc4EsA.woff2',
        e.data.base_uri + '/theme/fonts/Roboto/KFOlCnqEu92Fr1MmSU5fCxc4EsA.woff2',
        e.data.base_uri + '/theme/fonts/Roboto/KFOlCnqEu92Fr1MmSU5fChc4EsA.woff2',
        e.data.base_uri + '/theme/fonts/Roboto/KFOlCnqEu92Fr1MmSU5fBBc4.woff2',
        e.data.base_uri + '/theme/fonts/Roboto/KFOmCnqEu92Fr1Mu72xKOzY.woff2',
        e.data.base_uri + '/theme/fonts/Roboto/KFOmCnqEu92Fr1Mu5mxKOzY.woff2',
        e.data.base_uri + '/theme/fonts/Roboto/KFOmCnqEu92Fr1Mu7mxKOzY.woff2',
        e.data.base_uri + '/theme/fonts/Roboto/KFOmCnqEu92Fr1Mu4WxKOzY.woff2',
        e.data.base_uri + '/theme/fonts/Roboto/KFOmCnqEu92Fr1Mu7WxKOzY.woff2',
        e.data.base_uri + '/theme/fonts/Roboto/KFOmCnqEu92Fr1Mu7GxKOzY.woff2',
        e.data.base_uri + '/theme/fonts/Roboto/KFOmCnqEu92Fr1Mu4mxK.woff2',
        e.data.base_uri + '/theme/fonts/Roboto/KFOlCnqEu92Fr1MmEU9fCRc4EsA.woff2',
        e.data.base_uri + '/theme/fonts/Roboto/KFOlCnqEu92Fr1MmEU9fABc4EsA.woff2',
        e.data.base_uri + '/theme/fonts/Roboto/KFOlCnqEu92Fr1MmEU9fCBc4EsA.woff2',
        e.data.base_uri + '/theme/fonts/Roboto/KFOlCnqEu92Fr1MmEU9fBxc4EsA.woff2',
        e.data.base_uri + '/theme/fonts/Roboto/KFOlCnqEu92Fr1MmEU9fCxc4EsA.woff2',
        e.data.base_uri + '/theme/fonts/Roboto/KFOlCnqEu92Fr1MmEU9fChc4EsA.woff2',
        e.data.base_uri + '/theme/fonts/Roboto/KFOlCnqEu92Fr1MmEU9fBBc4.woff2',
        e.data.base_uri + '/theme/fonts/Roboto/KFOlCnqEu92Fr1MmWUlfCRc4EsA.woff2',
        e.data.base_uri + '/theme/fonts/Roboto/KFOlCnqEu92Fr1MmWUlfABc4EsA.woff2',
        e.data.base_uri + '/theme/fonts/Roboto/KFOlCnqEu92Fr1MmWUlfCBc4EsA.woff2',
        e.data.base_uri + '/theme/fonts/Roboto/KFOlCnqEu92Fr1MmWUlfBxc4EsA.woff2',
        e.data.base_uri + '/theme/fonts/Roboto/KFOlCnqEu92Fr1MmWUlfCxc4EsA.woff2',
        e.data.base_uri + '/theme/fonts/Roboto/KFOlCnqEu92Fr1MmWUlfChc4EsA.woff2',
        e.data.base_uri + '/theme/fonts/Roboto/KFOlCnqEu92Fr1MmWUlfBBc4.woff2',
    ]));
});

self.addEventListener('push', function (e) {
    var json = e.data.json();
    var options = {
        body: json.body,
        icon: json.picture,
        vibrate: [100, 50, 100],
        data: { url: json.action },
        actions: [{ action: "action", title: json.button }]
    };
    e.waitUntil(
        self.registration.showNotification(json.title, options)
    );
});

self.addEventListener('notificationclick', function (e) {
    e.notification.close();

    e.waitUntil(clients.matchAll({
        type: 'window'
    }).then(function (clientList) {
        for (var i = 0; i < clientList.length; i++) {
            var client = clientList[i];

            if (client.url == e.notification.data.url && 'focus' in client) {
                return client.focus();
            }
        }

        if (clients.openWindow) {
            return clients.openWindow(e.notification.data.url);
        }
    }));
}
    , false);

self.addEventListener('fetch', (e) => {
    e.respondWith(
        caches.match(e.request).then((response) => response || fetch(e.request)),
    );
});
