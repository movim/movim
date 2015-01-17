function postStart() {
    if(localStorage.postStart == 1) {
        // We disable the notifications for a couple of seconds
        Notification.inhibit(10);
        
        Presence_ajaxSetPresence();
        //Presence_ajaxConfigGet();
        Presence_ajaxServerCapsGet();
        Presence_ajaxBookmarksGet();
        Menu_ajaxRefresh();
        localStorage.postStart = 0;
    }
}

function setPresenceActions() {
    var textarea = document.querySelector('textarea.status');

    if(textarea != null) {
        textarea.onkeypress = function(event) {
            if(event.keyCode == 13) {
                Presence_ajaxSetStatus(this.value);
                this.blur();
            }
        };

        textarea.onfocus = function(event) {
            movim_textarea_autoheight(this);
        };
    }
}

MovimWebsocket.attach(function()
{
    setPresenceActions();
    postStart();
});
