MovimWebsocket.attach(function() {
    Notification.current('community');
});

MovimWebsocket.initiate(() => Communities_ajaxHttpGet());
