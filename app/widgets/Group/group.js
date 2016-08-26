var Group = {
    addLoad: function(id) {
        document.getElementById('group_widget').className = 'card shadow spin';
        MovimUtils.addClass('#group_widget', 'on');
        MovimUtils.addClass('#group_widget', id);
    },

    clearLoad: function() {
        MovimUtils.removeClass('#group_widget', 'on');
    }
}
