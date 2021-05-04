var Preview = {
    copyToClipboard: function(text) {
        MovimUtils.copyToClipboard(text);
        Toast.send(Preview.copyNotification);
    }
}

movimAddOnload(function() {
    document.addEventListener('keydown', function(e) {
        if (document.querySelector('#preview').innerHTML != '' && e.keyCode == 27) {
            Preview_ajaxHttpHide();
        }
    }, false);
    document.addEventListener('click', function(e) {
        if (document.querySelector('#preview').innerHTML == '') return;

        if (!document.querySelector('#preview img').contains(e.target)
        && !document.querySelector('#preview .prevnext.prev').contains(e.target)
        && !document.querySelector('#preview .prevnext.next').contains(e.target)
        && !document.querySelector('#preview .buttons').contains(e.target)) {
            Preview_ajaxHttpHide();
        }
    }, false);
});
