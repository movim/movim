var CommunityPosts = {
    getItems: function () {
        var parts = MovimUtils.urlParts();
        if (parts.params.length > 0) {
            var node = (parts.params[1] == undefined || parts.params[1] == '')
                ? 'urn:xmpp:microblog:0'
                : parts.params[1];

            CommunityPosts_ajaxGetItems(parts.params[0], node, parts.params[2]);
        }
    }
}

MovimWebsocket.attach(function () {
    CommunityPosts.getItems();
});
