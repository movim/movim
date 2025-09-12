var AdHoc = {
    refresh: function () {
        var items = document.querySelectorAll('.adhoc_widget .actions li:not(.subheader)');
        var i = 0;

        while (i < items.length) {
            items[i].onclick = function () {
                Drawer.clear();
                AdHoc_ajaxCommand(this.dataset.jid, this.dataset.node);
            };

            i++;
        }
    },
    initForm: function () {
        var textareas = document.querySelectorAll('#dialog form[name=command] textarea');
        var i = 0;

        while (i < textareas.length) {
            MovimUtils.textareaAutoheight(textareas[i]);
            i++;
        }

        var form = document.querySelector('#dialog form[name=command]');

        if (form) {
            form.addEventListener('input', e => AdHoc.checkFormValidity());
        }

        AdHoc.checkFormValidity();
    },
    submit: function (jid) {
        var form = document.querySelector('#dialog form[name=command]');
        AdHoc_ajaxSubmit(jid, form.dataset.node, MovimUtils.formToJson('command'), form.dataset.sessionid);
    },
    checkFormValidity: function () {
        var form = document.querySelector('#dialog form[name=command]');
        var action = document.querySelector('#dialog #adhoc_action');

        if (form && action) {
            if (form.checkValidity()) {
                action.classList.remove('disabled');
            } else {
                action.classList.add('disabled');
            }
        }
    }
}

MovimWebsocket.attach(function () {
    var parts = MovimUtils.urlParts();

    if (parts.page === "configuration") {
        AdHoc_ajaxGet();
    }
});
