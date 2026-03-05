MovimWebsocket.attach(function () {
    if (MovimUtils.urlParts().page == 'space') {
        SpaceInfo_ajaxHttpGet(
            MovimUtils.urlParts().params[0],
            MovimUtils.urlParts().params[1]
        );
    }
});
