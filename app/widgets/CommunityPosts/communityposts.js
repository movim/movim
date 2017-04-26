MovimWebsocket.attach(function() {
    var parts = MovimUtils.urlParts();
    if(parts.params.length > 0) {
        var node = (parts.params[1] == undefined) ? 'urn:xmpp:microblog:0' : parts.params[1];

        CommunityPosts_ajaxGetItems(parts.params[0], node);
    }
});
