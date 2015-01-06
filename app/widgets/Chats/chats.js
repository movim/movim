var Chats = {
    refresh: function() {
        var items = document.querySelectorAll('ul#chats_widget_list li:not(.subheader)');
        var i = 0;
        while(i < items.length)
        {
            items[i].onclick = function(e) {
                Chat_ajaxGet(this.dataset.jid);
                Chats.reset(items);
                Notification_ajaxClear('chat|' + this.dataset.jid);
                Notification_ajaxCurrent('chat|' + this.dataset.jid);
                movim_add_class(this, 'active');
            }

            items[i].onmousedown = function(e) {
                if(e.which == 2) {
                    Chats_ajaxClose(this.dataset.jid);
                    MovimTpl.hidePanel();
                }
            }

            i++;
        }

        if(window.innerWidth > 1024 && !MovimTpl.isPanel()) {
            items[0].click();
        }
    },

    reset: function(list) {
        for(i = 0; i < list.length; i++) {
            movim_remove_class(list[i], 'active');
        }
    }
}

MovimWebsocket.attach(function() {
    Chats.refresh();
});
