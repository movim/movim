var Chat = {
    /*
    message: function(jid, html) {
        movim_append('messages' + jid, html);
    },*/
    addSmiley: function(smiley) {

    },
    sendMessage: function(n, jid)
    {
        var text = n.value;
        n.value = "";
        n.focus();
        return encodeURIComponent(text);
    },
    notify : function(title, body, image)
    {
        if(document_focus == false) {
            movim_title_inc();
            movim_desktop_notification(title, body, image);
        }
    }
}

var state = 0;
