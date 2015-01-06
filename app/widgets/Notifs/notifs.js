/*function showNotifsList() {
    movim_toggle_class('#notifslist', 'show');
}*/

var Notifs = {
    refresh : function() {
        var items = document.querySelectorAll('#notifs_widget li:not(.subheader)');
        var i = 0;
        while(i < items.length)
        {
            items[i].onclick = function(e) {
                MovimTpl.showPanel();
                Contact_ajaxGetContact(this.dataset.jid);
                Notifs.reset(items);
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
    Notifs.refresh();
});
