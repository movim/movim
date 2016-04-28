var Search = {
    init : function() {
        document.querySelector('input[name=keyword]').focus();
    }
}

document.addEventListener('keydown', function(e) {
    if (e.keyCode == 77 && e.ctrlKey) {
        Search_ajaxRequest();
    }
});
