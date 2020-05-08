var Drawer = {
    filled : function() {
        return (document.querySelector('#drawer').innerHTML != '');
    },
    clear : function() {
        Drawer_ajaxClear();
    },
    toggle : function(e) {
        if (Drawer.filled()
        && document.querySelector('body') == e.target) {
            Drawer_ajaxClear();
        }
    }
}

movimAddOnload(function() {
    document.body.addEventListener('click', Drawer.toggle, false);
    document.addEventListener('keydown', function(e) {
        if (Drawer.filled()
        && e.keyCode == 27) {
            Drawer.clear();
        }
    }, false);
});
