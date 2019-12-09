MovimWebsocket.attach(function() {
    Notification.current('community');
});

movim_add_onload(e => Communities_ajaxHttpGet());
