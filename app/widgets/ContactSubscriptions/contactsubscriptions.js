MovimWebsocket.attach(function() {
    if (!MovimUtils.isMobile()) {
        var parts = MovimUtils.urlParts();
        if (parts.params.length > 0) {
            ContactSubscriptions_ajaxRefresh(parts.params[0]);
        }
    }
});
