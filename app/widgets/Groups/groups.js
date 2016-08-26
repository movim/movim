var Groups = {
    refresh: function() {
        var items = document.querySelectorAll('#groups_widget ul li:not(.subheader)');

        var i = 0;
        while(i < items.length)
        {
            items[i].onclick = function(e) {
                MovimTpl.scrollPanelTop();
                Group_ajaxGetItems(this.dataset.server, this.dataset.node);
                Group_ajaxGetSubscriptions(this.dataset.server, this.dataset.node, false);
                MovimUtils.removeClassInList('active', items);
                MovimUtils.addClass(this, 'active');
            };
            i++;
        }
    }
};

MovimWebsocket.attach(function() {
    Notification.current('groups');
    Groups_ajaxHeader();
    Groups.refresh();
})
