var Config = {
    switchNightMode: function()
    {
        document.body.classList.toggle('nightmode');
    }
}

MovimWebsocket.attach(function() {
    Config_ajaxMAMGetConfig();
});
