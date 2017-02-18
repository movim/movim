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
    }
}

MovimWebsocket.attach(function()
{
    Presence_ajaxGetPresence();
});
