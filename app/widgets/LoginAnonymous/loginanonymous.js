window.onbeforeunload = function() {
    //Presence_ajaxLogout();
}

MovimWebsocket.attach(function() {
    // We register the socket
    MovimWebsocket.connection.register('anonymous.jappix.com');
});
