var Presence = {
    clearQuick : function() {
        localStorage.removeItem('quickDeviceId');
        localStorage.removeItem('quickLogin');
        localStorage.removeItem('quickHost');
        localStorage.removeItem('quickKey');
    }
}

MovimWebsocket.initiate(() => Presence_ajaxHttpGetPresence());
