var Presence = {
    refresh : function() {
        var textarea = document.querySelector('form[name=presence] textarea');

        if(textarea != null) {
            MovimUtils.textareaAutoheight(textarea);

            textarea.oninput = function(event) {
                MovimUtils.textareaAutoheight(this);
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
    },

    postStart : function() {
        if(localStorage.postStart == 1) {
            // We disable the notifications for a couple of seconds
            Notification.inhibit(10);

            Presence_ajaxClear();
            Presence_ajaxSet();
            //Presence_ajaxConfigGet();
            Presence_ajaxServerCapsGet();
            Presence_ajaxBookmarksGet();
            Presence_ajaxUserRefresh();
            Presence_ajaxFeedRefresh();
            Presence_ajaxServerDisco();
            //Menu_ajaxRefresh();
            localStorage.postStart = 0;
        }
    }
}

MovimWebsocket.attach(function()
{
    Presence.refresh();
    Presence.postStart();
    Presence_ajaxGetPresence();
});
