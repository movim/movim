MovimWebsocket.attach(function() {
    var parts = MovimUtils.urlParts();
    if (parts.params.length > 0) {
        CommunitiesServer_ajaxDisco(parts.params[0]);
    }
});
