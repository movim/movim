MovimWebsocket.attach(function() {
    Notification.current('community');
});

MovimWebsocket.initiate(e => Communities_ajaxHttpGet());
