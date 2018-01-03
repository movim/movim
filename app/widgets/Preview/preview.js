var Preview = {
    clear : function() {
        document.querySelector('#preview').innerHTML = '';
    }
}

movim_add_onload(function() {
    document.addEventListener('keydown', function(e) {
        if (document.querySelector('#preview').innerHTML != '' && e.keyCode == 27) {
            Preview.clear();
        }
    }, false);
});
