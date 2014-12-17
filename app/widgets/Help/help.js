var Help = {
    joinChatroom : function() {
        var hash = new H();
        hash.set('jid', 'movim@conference.movim.eu');
        hash.set('name', 'Movim Chatroom');
        hash.set('nick', false);
        hash.set('autojoin', 0);

        Bookmark_ajaxBookmarkMucAdd(hash);
        Bookmark_ajaxBookmarkMucJoin('movim@conference.movim.eu', '');
    }
}
