var Config = {
    switchNightMode: function () {
        document.body.classList.toggle('nightmode');
    },

    setAccentColor: function (color) {
        document.body.style.setProperty('--movim-accent', 'var(--p-' + color + ')');
    },

    updateSystemVariable: function (variable, value) {
        window[variable] = value;
    }
}

MovimWebsocket.attach(function () {
    Notif.current('configuration');

    Config_ajaxMAMGetConfig();
    Config_ajaxBlogGetConfig();
});
