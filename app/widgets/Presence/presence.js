function postStart() {
    if(localStorage.postStart == 1) {
        Presence_ajaxConfigGet();
        Presence_ajaxServerCapsGet();
        Presence_ajaxBookmarksGet();
        Presence_ajaxSetPresence();
        localStorage.postStart = 0;
    }
}

function setPresenceActions() {
    document.querySelector('#presence_widget textarea.status').onkeypress = function(event) {
        if(event.keyCode == 13) {
            Presence_ajaxSetStatus(this.value);
            this.blur();
        }
    };

    document.querySelector('#presence_widget #tab').onclick = function(event) {
        movim_toggle_class('#presence_widget', 'unfolded');
    };

    document.querySelector('#presence_widget #list .tab').onclick = function(event) {
        movim_toggle_class('#presence_widget', 'unfolded');
    };
}

movim_add_onload(function()
{
    setPresenceActions();
    postStart();
});
