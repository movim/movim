var ChatActions = {
    message: null,

    setMessage: function (message) {
        ChatActions.message = message;
    },
    focusSearch: function () {
        document.querySelector('form[name=search] input').focus();
    }
}

MovimEvents.registerWindow('keydown', 'search_message', (e) => {
    if (e.key == 'f' && e.ctrlKey && MovimUtils.urlParts().page == 'chat' && Chat.getTextarea()) {
        e.preventDefault();
        ChatActions_ajaxShowSearchDialog(Chat.getTextarea().dataset.jid, Boolean(Chat.getTextarea().dataset.muc));
    }
});
