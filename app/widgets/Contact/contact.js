MovimWebsocket.attach(function() {
    var parts = MovimUtils.urlParts();
    if(parts.params.length) {
        document.querySelector('#contact_widget').innerHTML = '';
        Contact_ajaxGetContact(parts.params[0]);
        MovimTpl.showPanel();
    }
});
