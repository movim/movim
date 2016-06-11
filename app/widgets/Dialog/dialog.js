var Dialog = {
    addScroll : function() {
        MovimUtils.addClass('#dialog', 'scroll');
    },
    filled : function() {
        if(document.querySelector('#dialog').innerHTML != '') { return true; }
        return false;
    },
    clear : function() {
        Dialog_ajaxClear();
    }
}
