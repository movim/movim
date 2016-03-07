var Stickers = {
    addSmiley: function(element) {
        var n = document.querySelector('#chat_textarea');
        n.value = n.value + element.dataset.emoji;
        n.focus();
        Dialog.clear();
    }
}
