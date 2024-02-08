var Preview = {
    fill: function (html) {
        MovimTpl.fill('#preview', html);
        MovimTpl.pushAnchorState('preview', function () { Preview_ajaxHttpHide() });
    },
    copyToClipboard: function(text) {
        MovimUtils.copyToClipboard(text);
        Preview_ajaxCopyNotify();
    }
}

movimAddOnload(function() {
    document.addEventListener('keydown', function(e) {
        if (document.querySelector('#preview').innerHTML != '' && e.key == 'Escape') {
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
