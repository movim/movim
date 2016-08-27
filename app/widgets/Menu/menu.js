var Menu = {
    refresh: function() {
        var items = document.querySelectorAll('ul#menu_wrapper li, #post_widget ul.card li');

        var i = 0;
        while(i < items.length)
        {
            if(items[i].id != 'history') {
                items[i].onclick = function(e) {
                    if(this.dataset.id) {
                        MovimTpl.showPanel();
                        Post_ajaxGetPost(this.dataset.server, this.dataset.node, this.dataset.id);
                        //Menu_ajaxGetNode(this.dataset.server, this.dataset.node);
                        MovimUtils.removeClassInList('active', items);
                        MovimUtils.addClass(this, 'active');
                    }
                }
            }
            i++;
        }
    }
};

MovimWebsocket.attach(function() {
    Notification_ajaxClear('news');
    Notification.current('news');
    Menu.refresh();
});
