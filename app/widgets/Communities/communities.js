var Communities = {
    morePosts: function(button, page) {
        button.remove();
        Communities_ajaxHttpMorePosts(page);
    }
}

MovimWebsocket.attach(function() {
    Notification.current('community');
});

MovimWebsocket.initiate(() => Communities_ajaxHttpGet());
