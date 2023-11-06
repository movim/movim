var Communities = {
    init: function() {
        let parts = MovimUtils.urlParts();
        let page = parts.params[0] !== undefined ? parseInt(parts.params[0]) : 0;

        if (parts.hash != '') {
            switch (parts.hash) {
                case 'communities':
                    Communities_ajaxHttpGetCommunities(page);
                    break;
                case 'contacts':
                    Communities_ajaxHttpGetContacts(page);
                    break;
            }
        } else {
            Communities_ajaxHttpGetAll(page);
        }
    },

    morePosts: function(button, page, type) {
        button.remove();
        Communities_ajaxHttpGetPosts(page, type);
    }
}

MovimWebsocket.attach(function() {
    Notif.current('community');
});

MovimWebsocket.initiate(() => Communities.init());
