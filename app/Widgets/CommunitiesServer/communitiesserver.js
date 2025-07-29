MovimWebsocket.attach(function() {
    Notif.current('community');
    var parts = MovimUtils.urlParts();
    if (parts.params.length > 0) {
        CommunitiesServer_ajaxDisco(parts.params[0]);
    }
});
