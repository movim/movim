var Dialog = {
    addScroll : function() {
        movim_add_class('#dialog', 'scroll');
    },
    filled : function() {
        if(document.querySelector('#dialog').innerHTML != '') { return true; }
        return false;
    },
    clear : function() {
        Dialog_ajaxClear();
    }
}
