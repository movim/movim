MovimWebsocket.attach(function() {
    if (!MovimUtils.isMobile()) {
        var parts = MovimUtils.urlParts();
        if (parts.params.length > 0) {
            CommunityData_ajaxGetAvatar(parts.params[0], parts.params[1]);
        }
    }
});