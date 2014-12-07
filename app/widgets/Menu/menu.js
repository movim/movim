var Menu = {
    refresh: function() {
        var items = document.querySelectorAll('#menu_widget ul li');

        var i = 0;
        while(i < items.length -1)
        {
            items[i].onclick = function(e) {
                Post_ajaxGetPost(this.dataset.id);
                Menu.reset(items);
                movim_add_class(this, 'active');
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
    Menu.refresh();
});
