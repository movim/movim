MovimWebsocket.initiate(() => {
    if (!MovimUtils.isMobile()) {
        NewsNav_ajaxHttpGet(
            MovimUtils.urlParts().page,
            MovimUtils.urlParts().params[0]
        )
    }
});