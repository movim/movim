var Presence = {
    clearQuick : function() {
        localStorage.removeItem('quickDeviceId');
        localStorage.removeItem('quickLogin');
        localStorage.removeItem('quickHost');
        localStorage.removeItem('quickKey');
    },
    setFirebaseToken : function(token) {
        Presence_ajaxSetFireBaseToken(token);
        Android.clearFirebaseToken();
    }
}

MovimWebsocket.initiate(() => Presence_ajaxHttpGetPresence());
