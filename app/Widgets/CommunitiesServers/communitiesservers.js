MovimWebsocket.initiate(() => CommunitiesServers_ajaxHttpGet());

MovimWebsocket.attach(function () {
    Notif.current('explore');
});