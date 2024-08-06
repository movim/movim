MovimWebsocket.attach(function() {
    Notif.current('contact');

    if (!MovimUtils.isMobile()) {
        var parts = MovimUtils.urlParts();

        ContactData_ajaxGet(parts.params[0]);

        if (parts.params.length > 0) {
            ContactData_ajaxRefresh(parts.params[0]);
        }
    }
});
