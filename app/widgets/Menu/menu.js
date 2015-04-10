var Menu = {
    refresh: function() {
        var items = document.querySelectorAll('#menu_widget ul li, #post_widget ul.card li');

        var i = 0;
        while(i < items.length)
        {
            if(items[i].id != 'history') {
                items[i].onclick = function(e) {
                    if(this.dataset.id) {
                        MovimTpl.showPanel();
                        Post_ajaxGetPost(this.dataset.id);
                        //Menu_ajaxGetNode(this.dataset.server, this.dataset.node);
                        Menu.reset(items);
                        movim_add_class(this, 'active');
                    }
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
    Notification_ajaxClear('news');
    Notification.current('news');
    Menu.refresh();
});
