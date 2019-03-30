var Post = {
    comment: function() {
        document.querySelector('#comment_add').classList.remove('hide');
    },
    share : function() {
        var parts = MovimUtils.urlParts();

        if (parts.params.length) {
            Post_ajaxShare(parts.params[0], parts.params[1], parts.params[2]);
        }
    },
    refreshComments : function() {
        var parts = MovimUtils.urlParts();

        if (parts.params.length) {
            Post_ajaxGetPostComments(parts.params[0], parts.params[1], parts.params[2]);
        }
    }
};

MovimWebsocket.attach(function() {
    Notification.current('post');

    var parts = MovimUtils.urlParts();

    if (parts.params.length) {
        Post_ajaxGetPost(parts.params[0], parts.params[1], parts.params[2]);
    } else {
        Post_ajaxClear();
    }
});
