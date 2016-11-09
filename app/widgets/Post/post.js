MovimWebsocket.attach(function() {
    var parts = MovimUtils.urlParts();

    if(parts.params.length) {
        document.querySelector('#post_widget').innerHTML = '';
        if(parts.params[0] == 'publish') {
            Publish_ajaxCreateBlog();
        } else {
            Post_ajaxGetPost(parts.params[0], parts.params[1], parts.params[2]);
        }

        MovimTpl.showPanel();
    } else {
        Post_ajaxClear();
    }
});
