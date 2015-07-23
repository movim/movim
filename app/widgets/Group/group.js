var Group = {
    addLoad: function(id) {
        document.querySelector('#group_widget').className = 'card shadow spinner';
        movim_add_class('#group_widget', 'on');
        movim_add_class('#group_widget', id);
    },

    clearLoad: function() {
        movim_remove_class('#group_widget', 'on');
    },

    enableVideos: function() {
        var items = document.querySelectorAll('video');

        var i = 0;
        while(i < items.length)
        {
            items[i].setAttribute('controls', 'controls');
            i++;
        }
    }
}
