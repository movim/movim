var Share = {
    get: function() {
        var parts = MovimUtils.urlParts();
        if (parts.params[0]) {
            console.log(parts.params[0]);
            uri = parts.params[0].substr(0, 5) == 'xmpp:'
                ? parts.params[0]
                : atob(parts.params[0]);

            document.querySelector('h4').innerHTML = uri;
            Share_ajaxHttpGet(uri);
        }
    },
    redirect: function(url) {
        MovimUtils.redirect(url);
    }
};

MovimWebsocket.attach(function() {
    Share.get();
});
