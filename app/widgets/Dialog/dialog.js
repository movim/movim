var Dialog = {
    toggleScroll : function() {
        movim_toggle_class('#dialog', 'scroll');
    },
    filled : function() {
        if(document.querySelector('#dialog').innerHTML != '') { return true; }
        return false;
    },
    clear : function() {
        movim_remove_class('#dialog', 'scroll');
        document.querySelector('#dialog').innerHTML = '';
    }
}
