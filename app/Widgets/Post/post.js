var Post = {
    comment: function() {
        document.querySelector('#comment_add').classList.remove('hide');
        document.querySelector('#comment_add textarea').focus();
        document.querySelector('#comment_add').scrollIntoView();
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
    },
    checkCommentAction: function() {
        var parts = MovimUtils.urlParts();
        if (parts.hash == 'comment') {
            Post.comment();
        }
    }
};

MovimWebsocket.attach(function() {
    Notif.current('post');

    var parts = MovimUtils.urlParts();

    if (parts.params.length == 3) {
        Post_ajaxGetPost(parts.params[0], parts.params[1], parts.params[2]);
    } else {
        Post_ajaxGetNotFound();
    }
});
