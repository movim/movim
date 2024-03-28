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
        if (Dialog.filled()
        && !Dialog.locked()) {
            Dialog_ajaxClear();
        }
    },
}

MovimEvents.registerBody('click', 'dialog', () => Dialog.clear());
MovimEvents.registerBody('keydown', 'dialog', (e) =>  {
    if (e.key == 'Escape') {
        Dialog.clear();
    }
});
