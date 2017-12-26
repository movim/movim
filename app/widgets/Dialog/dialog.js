var Dialog = {
    addScroll : function() {
        document.querySelector('#dialog').classList.add('scroll');
    },
    filled : function() {
        return (document.querySelector('#dialog').innerHTML != '');
    },
    clear : function() {
        Dialog_ajaxClear();
    },
    toggle : function(e) {
        if(Dialog.filled()
        && document.querySelector('body') == e.target) {
            Dialog_ajaxClear();
        }
    },
}

movim_add_onload(function() {
    document.body.addEventListener('click', Dialog.toggle, false);
    document.addEventListener('keydown', function(e) {
        if(Dialog.filled()
        && e.keyCode == 27) {
            Dialog.clear();
        }
    }, false);
});
