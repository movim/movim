var Chats = {
    startX: 0,

    refresh: function() {
        var list = document.querySelector('#chats_widget_list');
        list.innerHTML = list.innerHTML.trim();

        var items = document.querySelectorAll('ul#chats_widget_list li:not(.subheader)');
        var i = 0;

        while(i < items.length)
        {
            if (items[i].dataset.jid != null) {
                items[i].onclick = function(e) {
                    Rooms.refresh();

                    Chat_ajaxGet(this.dataset.jid);

                    items.forEach(item => item.classList.remove('active'));
                    this.classList.add('active');
                };

                items[i].onmousedown = function(e) {
                    if (e.which == 2) {
                        Chats_ajaxClose(this.dataset.jid, (MovimUtils.urlParts().params[0] === this.dataset.jid));
                        e.preventDefault();
                    }
                }

                items[i].addEventListener('touchstart', function(event) {
                    Chats.startX = event.targetTouches[0].pageX;
                }, true);

                items[i].addEventListener('touchmove', function(event) {
                    move = Math.abs(parseInt(event.targetTouches[0].pageX - Chats.startX));

                    if (move > this.offsetWidth/2) {
                        this.classList.add('close');
                    }

                    this.style.transform = 'matrix3d(1, 0, 0, 0, 0, 1, 0, 0, 0, 0, 1, 0, '
                        + parseInt(event.targetTouches[0].pageX - Chats.startX)
                        +', 0, 0, 1)';
                }, true);

                items[i].addEventListener('touchend', function(event) {
                    move = Math.abs(parseInt(event.changedTouches[0].pageX - Chats.startX));

                    if (move > this.offsetWidth/2) {
                        Chats_ajaxClose(this.dataset.jid, (MovimUtils.urlParts().params[0] === this.dataset.jid));
                    }

                    this.style.transform = '';
                    this.classList.remove('close');
                    Chats.startX = 0;
                }, true);
            }

            items[i].classList.remove('active');
            i++;
        }

        Notification_ajaxGet();
    },
    prepend: function(from, html) {
        MovimTpl.remove('#' + MovimUtils.cleanupId(from + '_chat_item'));
        MovimTpl.prepend('#chats_widget_list', html);
        Chats.refresh();
        Notification_ajaxGet();
    }
};

MovimWebsocket.attach(function() {
    Chats_ajaxGet();
});
