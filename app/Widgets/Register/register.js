MovimWebsocket.register(function()
{
    Register_ajaxGetForm(MovimUtils.urlParts().params[0]);
});

MovimWebsocket.attach(function() {
    Notif.current('register');

    var domain = MovimUtils.urlParts().params[0];
    MovimWebsocket.connection.register(domain);
    Register.host = domain;
});
