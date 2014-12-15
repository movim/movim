function postStart() {
    if(localStorage.postStart == 1) {
        Presence_ajaxSetPresence();
        //Presence_ajaxConfigGet();
        Presence_ajaxServerCapsGet();
        Presence_ajaxBookmarksGet();
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

    /*
    document.querySelector('#presence_widget #tab').onclick = function(event) {
        movim_toggle_class('#presence_widget', 'unfolded');
    };

    document.querySelector('#presence_widget #list .tab').onclick = function(event) {
        movim_toggle_class('#presence_widget', 'unfolded');
    };*/
}

MovimWebsocket.attach(function()
{
    setPresenceActions();
    postStart();
});
