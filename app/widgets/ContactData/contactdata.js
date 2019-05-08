MovimWebsocket.attach(function() {
    Notification.current('contact');
    var parts = MovimUtils.urlParts();
    if (parts.params.length > 0) {
        ContactData_ajaxRefresh(parts.params[0]);
    }
});
