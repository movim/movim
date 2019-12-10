var Presence = {
    clearQuick : function() {
        localStorage.removeItem('quickDeviceId');
        localStorage.removeItem('quickLogin');
        localStorage.removeItem('quickHost');
        localStorage.removeItem('quickKey');
    }
}

MovimWebsocket.initiate(e => Presence_ajaxHttpGetPresence());
