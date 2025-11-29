var Share = {
    get: function () {
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
