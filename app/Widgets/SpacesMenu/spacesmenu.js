var SpacesMenu = {
    get: function (server, node, route) {
        if (MovimUtils.urlParts().page == 'space') {
            SpacesMenu_ajaxHttpGet(server, node, MovimUtils.isMobile());
        } else {
            MovimUtils.reload(route);
        }
    }
}

MovimWebsocket.attach(() => {
    if (MovimUtils.urlParts().page == 'space') {
        SpacesMenu_ajaxHttpGet(
            MovimUtils.urlParts().params[0],
            MovimUtils.urlParts().params[1],
            MovimUtils.isMobile());
    };
});