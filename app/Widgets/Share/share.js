var Share = {
    get: function () {
        // https://developer.mozilla.org/en-US/docs/Web/Progressive_web_apps/Manifest/Reference/share_target
        if (window.location.search) {
            params = new URLSearchParams(window.location.search);

            url = null;

            if (url = params.get('url')) {
                Share.openUri(url);
                return;
            } else if (params.get('description')) {
                try {
                    url = new URL(params.get('description'));
                    Share.openUri(url);
                    return;
                } catch (error) { }
            }
        }

        var parts = MovimUtils.urlParts();

        if (parts.params[0]) {
            uri = parts.params[0].substr(0, 5) == 'xmpp:'
                ? parts.params[0]
                : atob(parts.params[0]);

            Share.openUri(uri);
        }
    },

    openUri: function (uri) {
        document.querySelector('h4').innerHTML = uri;
        Share_ajaxGet(uri);
    }
};

MovimWebsocket.attach(function () {
    Share.get();
});
