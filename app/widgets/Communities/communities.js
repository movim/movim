var Communities = {
    morePosts: function(button, page, type) {
        button.remove();
        Communities_ajaxHttpGetPosts(page, type);
    }
}

MovimWebsocket.attach(function() {
    Notif.current('community');
});

MovimWebsocket.initiate(() => Communities_ajaxHttpGetAll());
