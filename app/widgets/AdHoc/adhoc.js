var AdHoc = {
    refresh: function() {
        var items = document.querySelectorAll('#adhoc_widget li');
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
            movim_textarea_autoheight(textareas[i]);
            i++;
        }
    },
    submit: function() {
        var form = document.querySelector('#dialog form[name=command]');
        AdHoc_ajaxSubmit(movim_parse_form('command'), form.dataset.node, form.dataset.sessionid);
    }
}

MovimWebsocket.attach(function() {
    AdHoc_ajaxGet();
});
