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
        let tds = document.querySelectorAll('table.emojis td');
        let i = 0;

        while (i < tds.length) {
            tds[i].onclick = function() {
                if (mid) {
                    Chat_ajaxHttpSendReaction(mid, this.dataset.emoji);
                    Dialog_ajaxClear();
                } else {
                    Stickers.addSmiley(this);
                }
            }

            i++;
        }
    }
}
