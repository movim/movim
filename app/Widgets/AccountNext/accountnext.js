MovimWebsocket.register(function()
{
    AccountNext_ajaxGetForm(MovimUtils.urlParts().params[0]);
});

MovimWebsocket.attach(function() {
    Notif.current('accountnext');

    var domain = MovimUtils.urlParts().params[0];
    MovimWebsocket.connection.register(domain);
    AccountNext.host = domain;
});
