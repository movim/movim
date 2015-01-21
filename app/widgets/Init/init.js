var Init = {
    checkNode : function() {
        // TODO : very ugly, need to refactor this
        var jid = localStorage.getItem("username").replace("@", "at");
        var init = localStorage.getObject(jid + "_Init") || {};
        if(init.initialized != 'true') {
            Init_ajaxCreatePersistentStorage('storage:bookmarks');
            Init_ajaxCreatePersistentStorage('urn:xmpp:vcard4');
            Init_ajaxCreatePersistentStorage('urn:xmpp:avatar:data');
            Init_ajaxCreatePersistentStorage('http://jabber.org/protocol/geoloc');
            Init_ajaxCreatePersistentStorage('urn:xmpp:pubsub:subscription');
            Init_ajaxCreatePersistentStorage('urn:xmpp:microblog:0');
        }
    },
    setNode : function(node) {
        // TODO : need to refactor this too
        var jid = localStorage.getItem("username").replace("@", "at");
        var init = localStorage.getObject(jid + "_Init") || {};
        init.initialized = 'true';
        localStorage.setObject(jid + "_Init", init);
    }
}

MovimWebsocket.attach(function()
{
    Init.checkNode();
});
