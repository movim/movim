var version = 5;
var cacheKey = 'movim_' + version;

self.addEventListener('install', (e) => {
    // Workaround for https://issues.chromium.org/issues/466790291
    e.addRoutes({
        condition: {
            urlPattern: new URLPattern({})
        },
        source: "fetch-event"
    });

    e.waitUntil(
        caches.open(cacheKey).then((cache) => cache.addAll([
            '/theme/audio/call.opus',
            '/theme/audio/message.ogg',
            '/theme/fonts/MaterialSymbols/font.css',
            '/theme/fonts/MaterialSymbols/MaterialSymbols-Outlined.woff2',
            '/theme/fonts/Roboto/font.css',
            '/theme/fonts/Roboto/KFOlCnqEu92Fr1MmSU5fCRc4EsA.woff2',
            '/theme/fonts/Roboto/KFOlCnqEu92Fr1MmSU5fABc4EsA.woff2',
            '/theme/fonts/Roboto/KFOlCnqEu92Fr1MmSU5fCBc4EsA.woff2',
            '/theme/fonts/Roboto/KFOlCnqEu92Fr1MmSU5fBxc4EsA.woff2',
            '/theme/fonts/Roboto/KFOlCnqEu92Fr1MmSU5fCxc4EsA.woff2',
            '/theme/fonts/Roboto/KFOlCnqEu92Fr1MmSU5fChc4EsA.woff2',
            '/theme/fonts/Roboto/KFOlCnqEu92Fr1MmSU5fBBc4.woff2',
            '/theme/fonts/Roboto/KFOmCnqEu92Fr1Mu72xKOzY.woff2',
            '/theme/fonts/Roboto/KFOmCnqEu92Fr1Mu5mxKOzY.woff2',
            '/theme/fonts/Roboto/KFOmCnqEu92Fr1Mu7mxKOzY.woff2',
            '/theme/fonts/Roboto/KFOmCnqEu92Fr1Mu4WxKOzY.woff2',
            '/theme/fonts/Roboto/KFOmCnqEu92Fr1Mu7WxKOzY.woff2',
            '/theme/fonts/Roboto/KFOmCnqEu92Fr1Mu7GxKOzY.woff2',
            '/theme/fonts/Roboto/KFOmCnqEu92Fr1Mu4mxK.woff2',
            '/theme/fonts/Roboto/KFOlCnqEu92Fr1MmEU9fCRc4EsA.woff2',
            '/theme/fonts/Roboto/KFOlCnqEu92Fr1MmEU9fABc4EsA.woff2',
            '/theme/fonts/Roboto/KFOlCnqEu92Fr1MmEU9fCBc4EsA.woff2',
            '/theme/fonts/Roboto/KFOlCnqEu92Fr1MmEU9fBxc4EsA.woff2',
            '/theme/fonts/Roboto/KFOlCnqEu92Fr1MmEU9fCxc4EsA.woff2',
            '/theme/fonts/Roboto/KFOlCnqEu92Fr1MmEU9fChc4EsA.woff2',
            '/theme/fonts/Roboto/KFOlCnqEu92Fr1MmEU9fBBc4.woff2',
            '/theme/fonts/Roboto/KFOlCnqEu92Fr1MmWUlfCRc4EsA.woff2',
            '/theme/fonts/Roboto/KFOlCnqEu92Fr1MmWUlfABc4EsA.woff2',
            '/theme/fonts/Roboto/KFOlCnqEu92Fr1MmWUlfCBc4EsA.woff2',
            '/theme/fonts/Roboto/KFOlCnqEu92Fr1MmWUlfBxc4EsA.woff2',
            '/theme/fonts/Roboto/KFOlCnqEu92Fr1MmWUlfCxc4EsA.woff2',
            '/theme/fonts/Roboto/KFOlCnqEu92Fr1MmWUlfChc4EsA.woff2',
            '/theme/fonts/Roboto/KFOlCnqEu92Fr1MmWUlfBBc4.woff2',
        ]))
    );
});

self.addEventListener('activate', function (e) {
    e.waitUntil(caches.keys().then(keys => {
        return Promise.all(keys
            .filter(key => key != cacheKey)
            .map(key => caches.delete(key))
        );
    }).then(function () {
        return self.clients.claim();
    }));
});

self.addEventListener('push', function (e) {
    var json = e.data.json();
    var options = {
        body: json.body,
        icon: json.picture,
        badge: '/theme/img/app/badge.png',
        vibrate: [100, 50, 100],
        data: { url: json.action },
        actions: [{ action: json.action, title: json.actionButton }],
        timestamp: json.timestamp * 1000,
        tag: json.group,
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
        console.log(clientList)
        for (var i = 0; i < clientList.length; i++) {
            var client = clientList[i];

            if (client.url == e.notification.data.url && 'focus' in client) {
                return client.focus();
            }
        }

        if (clientList.length > 0) {
            return clientList[0].postMessage({ type: 'navigate', url: e.notification.data.url });
        } else if (clients.openWindow) {
            return clients.openWindow(e.notification.data.url);
        }
    }));
}, false);

self.addEventListener('fetch', (e) => {
    e.respondWith(
        caches.match(e.request).then((response) => response || fetch(e.request)),
    );
});
