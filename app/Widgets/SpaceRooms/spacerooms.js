MovimWebsocket.attach(function () {
    if (MovimUtils.urlParts().page == 'space') {
        SpaceRooms_ajaxHttpGet(
            MovimUtils.urlParts().params[0],
            MovimUtils.urlParts().params[1]
        );

        if (!MovimUtils.isMobile()) {
            SpaceRooms_ajaxHttpGetChat(
                MovimUtils.urlParts().params[0],
                MovimUtils.urlParts().params[1],
                MovimUtils.urlParts().params[2]);
        }
    }

    Notif.current('space');
});
