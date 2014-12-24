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
}
