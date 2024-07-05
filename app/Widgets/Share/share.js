var Share = {
    get: function() {
        var parts = MovimUtils.urlParts();
        if (parts.params[0]) {
            document.querySelector('h4').innerHTML = atob(parts.params[0]);
            Share_ajaxGet(atob(parts.params[0]));
        }
    },
    redirect: function(url) {
        MovimUtils.redirect(url);
    }
};

MovimWebsocket.attach(function() {
    Share.get();
});
