var Preview = {
    fill: function (html) {
        MovimTpl.fill('#preview', html);
        MovimTpl.pushAnchorState('preview', function () { Preview_ajaxHttpHide() });
    },
    copyToClipboard: function (text) {
        MovimUtils.copyToClipboard(text);
        Preview_ajaxCopyNotify();
    }
}

MovimEvents.registerBody('click', 'preview', (e) => {
    if (document.querySelector('#preview').innerHTML == '') return;

    if (!document.querySelector('#preview img').contains(e.target)
        && !document.querySelector('#preview .prevnext.prev').contains(e.target)
        && !document.querySelector('#preview .prevnext.next').contains(e.target)
        && !document.querySelector('#preview .buttons').contains(e.target)) {
        history.back()
    }
});

MovimEvents.registerBody('keydown', 'preview', (e) => {
    if (document.querySelector('#preview').innerHTML != '' && e.key == 'Escape') {
        history.back()
    }
});