MovimWebsocket.attach(function() {
    var nodeid = MovimUtils.urlParts().params[0];
    if(nodeid) {
        document.querySelector('#post_widget').innerHTML = '';
        Post_ajaxGetPost(nodeid);
        MovimTpl.showPanel();
    }
});
