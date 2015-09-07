var Groups = {
    refresh: function() {
        var items = document.querySelectorAll('#groups_widget ul li:not(.subheader)');

        var i = 0;
        while(i < items.length)
        {
            items[i].onclick = function(e) {
                MovimTpl.scrollPanelTop();
                Group_ajaxGetItems(this.dataset.server, this.dataset.node);
                Group_ajaxGetMetadata(this.dataset.server, this.dataset.node);
                Group_ajaxGetAffiliations(this.dataset.server, this.dataset.node);
                Group_ajaxGetSubscriptions(this.dataset.server, this.dataset.node, false);
                Groups.reset(items);
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
    Notification.current('groups');
    Groups_ajaxHeader();
    Groups.refresh();
});
