var Stickers = {
    addSmiley: function(element) {
        var n = document.querySelector('#chat_textarea');
        n.value = n.value + element.dataset.emoji;
        n.focus();
        Drawer.clear();
    },
    zoom: function(element, jid, pack, value) {
        var zoomed = document.querySelectorAll('.zoomed');

        if(element.classList.contains('zoomed')) {
            Stickers_ajaxSend(jid, pack, value);
            Drawer.clear();
        }

        var i = 0;
        while(i < zoomed.length)
        {
            zoomed[i].classList.remove('zoomed');
            i++;
        }

        element.classList.add('zoomed');
    }
}
