var Dialog = {
    addScroll : function() {
        document.querySelector('#dialog').classList.add('scroll');
    },
    addLocked : function() {
        document.querySelector('#dialog').classList.add('locked');
    },
    locked : function() {
        return document.querySelector('#dialog').classList.contains('locked');
    },
    filled : function() {
        return (document.querySelector('#dialog').innerHTML != '');
    },
    clear : function() {
        Dialog_ajaxClear();
    },
    toggle : function(e) {
        if (Dialog.filled()
        && !Dialog.locked()
        && document.querySelector('body') == e.target) {
            Dialog_ajaxClear();
        }
    },
}

movimAddOnload(function() {
    document.body.addEventListener('click', Dialog.toggle, false);
    document.addEventListener('keydown', function(e) {
        if (Dialog.filled()
        && !Dialog.locked()
        && e.keyCode == 27) {
            Dialog.clear();
        }
    }, false);
});
