var Group = {
    addLoad: function(id) {
        document.querySelector('#group_widget').className = 'card shadow spinner';
        MovimUtils.addClass('#group_widget', 'on');
        MovimUtils.addClass('#group_widget', id);
    },

    clearLoad: function() {
        MovimUtils.removeClass('#group_widget', 'on');
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
