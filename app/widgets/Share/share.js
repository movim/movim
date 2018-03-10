var Share = {
    get: function() {
        var parts = MovimUtils.urlParts();
        if (parts.params[0]) {
            document.querySelector('h4').innerHTML = parts.params[0];
            Share_ajaxGet(parts.params[0]);
        }
    },
    redirect: function(url) {
        MovimUtils.redirect(url);
    }
};

MovimWebsocket.attach(function() {
    Share.get();
});
