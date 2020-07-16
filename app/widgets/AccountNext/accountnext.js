var AccountNext = {
    host : '',
    setHost : function(host) {
        this.host = host;
    },
    setUsername : function(user) {
        document.querySelector('#username').innerHTML = user + '@' + this.host;
    }
}

function setUsername(user) {
    AccountNext.setUsername(user);
}

MovimWebsocket.register(function()
{
    AccountNext_ajaxGetForm(MovimUtils.urlParts().params[0]);
});

MovimWebsocket.attach(function() {
    Notification.current('accountnext');

    var domain = MovimUtils.urlParts().params[0];
    MovimWebsocket.connection.register(domain);
    AccountNext.host = domain;
});
