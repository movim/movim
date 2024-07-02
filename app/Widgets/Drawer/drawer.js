var Drawer = {
    open: function (key) {
        document.querySelector('#drawer').dataset.type = key;
        MovimTpl.pushAnchorState(key, function () { Drawer.clear(key) });
    },
    filled: function () {
        return (document.querySelector('#drawer').innerHTML != '');
    },
    hasTabs: function () {
        return Drawer.filled()
            && document.querySelector('#drawer').contains(document.querySelector('#navtabs'));
    },
    clear: function (key) {
        if (key && document.querySelector('#drawer').dataset.type == key) {
            Drawer_ajaxClear();
            return;
        }

        if (key == undefined) {
            MovimTpl.clearAnchorState();
            Drawer_ajaxClear();
        }
    }
}

MovimWebsocket.initiate(() => document.querySelector('#drawer').innerHTML = '');

MovimEvents.registerBody('click', 'drawer', (e) => {
    if (Drawer.filled()
    && document.querySelector('body') == e.target) {
        e.stopPropagation();
        history.back();
    }
});

MovimEvents.registerBody('keydown', 'drawer', (e) => {
    if (Drawer.filled()
        && e.key == 'Escape') {
        history.back();
    }
});
