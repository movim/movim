var Init = {
    checkNode : function() {
        if(localStorage.initialized != 'true') {
            Init_ajaxCreatePersistentStorage('storage:bookmarks');
            Init_ajaxCreatePersistentStorage('urn:xmpp:vcard4');
            Init_ajaxCreatePersistentStorage('urn:xmpp:avatar:data');
            Init_ajaxCreatePersistentStorage('http://jabber.org/protocol/geoloc');
            Init_ajaxCreatePersistentStorage('urn:xmpp:pubsub:subscription');
            Init_ajaxCreatePersistentStorage('urn:xmpp:microblog:0');
        }
    },
    setNode : function(node) {
        localStorage.initialized = 'true';
    }
}

MovimWebsocket.attach(function()
{
    Init.checkNode();
});
