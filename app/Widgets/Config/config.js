var Config = {
    switchNightMode: function()
    {
        document.body.classList.toggle('nightmode');
    }
}

MovimWebsocket.attach(function() {
    Notif.current('conf');

    Config_ajaxMAMGetConfig();
    Config_ajaxBlogGetConfig();
});
