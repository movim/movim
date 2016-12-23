var Post = {
    comment: function() {
        MovimUtils.showElement(document.querySelector('#comment_add'));
    },
    share : function() {
        var parts = MovimUtils.urlParts();

        if(parts.params.length) {
            Post_ajaxShare(parts.params[0], parts.params[1], parts.params[2]);
        }
    }
};

MovimWebsocket.attach(function() {
    var parts = MovimUtils.urlParts();

    if(parts.params.length) {
        Post_ajaxGetPost(parts.params[0], parts.params[1], parts.params[2]);
    } else {
        Post_ajaxClear();
    }
});
