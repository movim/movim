var Stickers = {
    addSmiley: function(element) {
        Chat.insertAtCursor(element.dataset.emoji);
        Drawer.clear();
    },
    zoom: function(element, jid, pack, value) {
        var zoomed = document.querySelectorAll('.zoomed');

        if (element.classList.contains('zoomed')) {
            Drawer.clear();

            var textarea = document.querySelector('#chat_textarea');
            Stickers_ajaxSend(jid, pack, value, Boolean(textarea.dataset.muc));
        }

        var i = 0;
        while(i < zoomed.length) {
            zoomed[i].classList.remove('zoomed');
            i++;
        }

        element.classList.add('zoomed');
    },
    setEmojisEvent(mid) {
        const emojis = document.querySelectorAll('#emojisearchbar + .emojis img');
        let i = 0;

        while (i < emojis.length) {
            emojis[i].onclick = function() {
                if (mid) {
                    Chat_ajaxHttpDaemonSendReaction(mid, this.dataset.emoji);
                } else {
                    Stickers.addSmiley(this);
                }

                Dialog_ajaxClear();
            }

            i++;
        }
    }
}
