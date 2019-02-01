var AdHoc = {
    refresh: function() {
        var items = document.querySelectorAll('#adhoc_widget li:not(.subheader)');
        var i = 0;

        while(i < items.length)
        {
            items[i].onclick = function() {
                AdHoc_ajaxCommand(this.dataset.jid, this.dataset.node);
            };

            i++;
        }
    },
    initForm: function() {
        var textareas = document.querySelectorAll('#dialog form[name=command] textarea');
        var i = 0;

        while(i < textareas.length)
        {
            MovimUtils.textareaAutoheight(textareas[i]);
            i++;
        }
    },
    submit: function(jid) {
        var form = document.querySelector('#dialog form[name=command]');
        AdHoc_ajaxSubmit(jid, MovimUtils.formToJson('command'),
            form.dataset.node, form.dataset.sessionid);
    }
}

MovimWebsocket.attach(function() {
    var parts = MovimUtils.urlParts();
    if (parts.page === "contact") {
        AdHoc_ajaxGet(parts.params[0]);
    } else {
        AdHoc_ajaxGet();
    }
});
