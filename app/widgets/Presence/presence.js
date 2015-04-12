function postStart() {
    if(localStorage.postStart == 1) {
        // We disable the notifications for a couple of seconds
        Notification.inhibit(10);
        
        Presence_ajaxSet();
        //Presence_ajaxConfigGet();
        Presence_ajaxServerCapsGet();
        Presence_ajaxBookmarksGet();
        Presence_ajaxUserRefresh();
        Presence_ajaxFeedRefresh();
        //Menu_ajaxRefresh();
        localStorage.postStart = 0;
    }
}

var Presence = {
    refresh : function() {
        var textarea = document.querySelector('form[name=presence] textarea');

        if(textarea != null) {
            movim_textarea_autoheight(textarea);

            textarea.onkeydown = function(event) {
                movim_textarea_autoheight(this);
            };
        }

        var presences = document.querySelectorAll('#dialog form ul li');

        var i = 0;
        while(i < presences.length)
        {
            presences[i].onclick = function(e) {
                this.querySelector('label').click();
            }
            i++;
        }
    }
}

MovimWebsocket.attach(function()
{
    Presence.refresh();
    postStart();
});
