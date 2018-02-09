movim_add_onload(function() {
    document.addEventListener('keydown', function(e) {
        if (document.querySelector('#preview').innerHTML != '' && e.keyCode == 27) {
            Preview_ajaxHide();
        }
    }, false);

    document.addEventListener('click', function(e) {
        if (document.querySelector('#preview').innerHTML == '') return;

        if (!document.querySelector('#preview img').contains(e.target)
        && !document.querySelector('#preview .buttons').contains(e.target)) {
            Preview_ajaxHide();
        }
    }, false);
});
