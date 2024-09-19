var Config = {
    switchNightMode: function()
    {
        document.body.classList.toggle('nightmode');
    },

    updateSystemVariable: function (variable, value) {
        window[variable] = value;
    }
}

MovimWebsocket.attach(function() {
    Notif.current('conf');

    Config_ajaxMAMGetConfig();
    Config_ajaxBlogGetConfig();
});
