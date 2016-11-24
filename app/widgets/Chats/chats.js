var Chats = {
    refresh: function() {
        var list = document.querySelector('#chats_widget_list');
        list.innerHTML = list.innerHTML.trim();

        var items = document.querySelectorAll('ul#chats_widget_list li:not(.subheader)');
        var i = 0;

        while(i < items.length)
        {
            if(items[i].dataset.jid != null) {
                items[i].onclick = function(e) {
                    Rooms.refresh();

                    Chat_ajaxGet(this.dataset.jid);
                    MovimUtils.removeClassInList('active', items);
                    Notification_ajaxClear('chat|' + this.dataset.jid);
                    Notification.current('chat|' + this.dataset.jid);
                    MovimUtils.addClass(this, 'active');
                };

                items[i].onmousedown = function(e) {
                    if(e.which == 2) {
                        Notification_ajaxClear('chat|' + this.dataset.jid);
                        Notification.current('');
                        Chats_ajaxClose(this.dataset.jid);
                        delete document.querySelector('#chat_widget').dataset.jid;
                        MovimTpl.hidePanel();
                    }
                }
            }

            MovimUtils.removeClass(items[i], 'active');

            i++;
        }

        Notification_ajaxGet();
    },
    prepend: function(from, html) {
        MovimTpl.remove('#' + MovimUtils.cleanupId(from + '_chat_item'));
        MovimTpl.prepend('#chats_widget_list', html);
        Chats.refresh();
        Notification_ajaxGet();
    }
};

MovimWebsocket.attach(function() {
    Chats_ajaxGet();
});
