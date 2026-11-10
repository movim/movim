var SpacesMenu = {
    get: function (server, node, route) {
        if (MovimUtils.urlParts().page == 'space') {
            SpacesMenu_ajaxHttpGet(server, node, null, MovimUtils.isMobile());
        } else {
            MovimUtils.reload(route);
        }
    },
    discoverJoin: function (uri) {
        if (button = document.querySelector('#spacesmenu_discover_add')) {
            button.onclick = () => {
                SpacesMenu_ajaxJoinFromUri(uri); Dialog_ajaxClear();
            }
        }
    }
}

MovimWebsocket.attach(() => {
    if (MovimUtils.urlParts().page == 'space') {
        SpacesMenu_ajaxHttpGet(
            MovimUtils.urlParts().params[0],
            MovimUtils.urlParts().params[1],
            MovimUtils.urlParts().params[2],
            MovimUtils.isMobile());
    };

    Notif.current('space');
});