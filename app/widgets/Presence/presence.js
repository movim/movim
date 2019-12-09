var Presence = {
    clearQuick : function() {
        localStorage.removeItem('quickDeviceId');
        localStorage.removeItem('quickLogin');
        localStorage.removeItem('quickHost');
        localStorage.removeItem('quickKey');
    }
}

movim_add_onload(e => Presence_ajaxHttpGetPresence());
