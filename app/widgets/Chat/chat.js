var Chat = {
    addSmiley: function(element) {
        var n = document.querySelector('#chat_textarea');
        n.value = n.value + element.dataset.emoji;
        Dialog.clear();    
    },
    sendMessage: function(jid)
    {
        var n = document.querySelector('#chat_textarea');
        var text = n.value;
        n.value = "";
        n.focus();
        return encodeURIComponent(text);
    },
    appendTextarea: function(value)
    { 
    },
    notify : function(title, body, image)
    {
        if(document_focus == false) {
            movim_title_inc();
            movim_desktop_notification(title, body, image);
        }
    },
    empty : function()
    {
        Chat_ajaxGet();
    }
}

var state = 0;
