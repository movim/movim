var Shortcuts = {
    clear: function (jid) {
        li = document.querySelector('#shortcuts_widget li[data-jid="' + jid + '"]');

        if (li) {
            li.classList.add('disappear');

            MovimTpl.closeMenu();

            setTimeout(e => {
                parent = li.parentNode;
                li.remove();
                if (parent) {
                    parent.innerHTML = parent.innerHTML.trim();
                }
            }, 200)
        }
    }
}

MovimWebsocket.attach(() => {
    Shortcuts_ajaxGet();
});