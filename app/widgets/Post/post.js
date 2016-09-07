MovimWebsocket.attach(function() {
    Post_ajaxClear();
    var parts = MovimUtils.urlParts();
    if(parts.params.length) {
        document.querySelector('#post_widget').innerHTML = '';
        Post_ajaxGetPost(parts.params[0], parts.params[1], parts.params[2]);
        MovimTpl.showPanel();
    }
});
