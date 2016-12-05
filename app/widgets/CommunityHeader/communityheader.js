MovimWebsocket.attach(function() {
    var parts = MovimUtils.urlParts();
    if(parts.params.length > 0) {
        CommunityHeader_ajaxGetMetadata(parts.params[0], parts.params[1]);
    }
});
