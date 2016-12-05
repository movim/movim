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

MovimWebsocket.attach(function() {
    var parts = MovimUtils.urlParts();
    if(parts.params.length > 0) {
        Group_ajaxGetAffiliations(parts.params[0], parts.params[1]);
        Group_ajaxGetItems(parts.params[0], parts.params[1]);
    }
});
