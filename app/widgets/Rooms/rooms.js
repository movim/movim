var Rooms = {
    refresh: function() {
        var items = document.querySelectorAll('#rooms_widget ul li:not(.subheader)');
        var i = 0;
        while(i < items.length)
        {
            if(items[i].dataset.jid != null) {
                items[i].onclick = function(e) {
                    console.log(this);
                    if(!movim_has_class(this, 'online')) {
                        if(this.dataset.nick != null) {
                            Rooms_ajaxJoin(this.dataset.jid, this.dataset.nick);
                        } else {
                            Rooms_ajaxJoin(this.dataset.jid);
                        }
                    }
                    
                    Chat_ajaxGetRoom(this.dataset.jid);
                    Chats.reset(items);
                    movim_add_class(this, 'active');
                }
            }

            i++;
        }
    },

    reset: function(list) {
        for(i = 0; i < list.length; i++) {
            movim_remove_class(list[i], 'active');
        }
    }
}

MovimWebsocket.attach(function() {
    Rooms.refresh();
});
