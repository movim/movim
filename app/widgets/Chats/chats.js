var Chats = {
    startX: 0,
    startY: 0,
    slideAuthorized: false,

    refresh: function(clearAllActives = false) {
        var list = document.querySelector('#chats_widget_list');
        var trim = list.innerHTML.trim();

        if (trim === '') list.innerHTML = trim;

        var items = document.querySelectorAll('ul#chats_widget_list li:not(.subheader)');
        var i = 0;

        while(i < items.length)
        {
            if (items[i].dataset.jid != null) {
                items[i].onclick = function(e) {
                    Rooms.refresh();
                    MovimUtils.addClass('ul#bottomnavigation', 'hidden');

                    Chat_ajaxGet(this.dataset.jid);

                    items.forEach(item => item.classList.remove('active'));
                    this.classList.add('active');
                };

                items[i].onmousedown = function(e) {
                    if (e.which == 2) {
                        Chats.closeItem(this, false);
                        e.preventDefault();
                    }
                }

                clientWidth = Math.abs(document.body.clientWidth);

                items[i].addEventListener('touchstart', function(event) {
                    Chats.startX = event.targetTouches[0].pageX;
                    Chats.startY = event.targetTouches[0].pageY;

                    this.classList.remove('moving');
                }, true);

                items[i].addEventListener('touchmove', function(event) {
                    moveX = Math.abs(parseInt(event.targetTouches[0].pageX - Chats.startX));
                    moveY = Math.abs(parseInt(event.targetTouches[0].pageY - Chats.startY));
                    delay = 20;

                    if (moveX > delay && moveX <= clientWidth) {
                        document.querySelector('#scroll_block').classList.add('freeze');
                        if (moveX > this.offsetWidth/2) {
                            this.classList.add('close');
                        } else {
                            this.classList.remove('close');
                        }

                        if (moveY < delay) {
                            Chats.slideAuthorized = true;
                        }

                        if (Chats.slideAuthorized) {
                            this.style.transform = 'matrix3d(1, 0, 0, 0, 0, 1, 0, 0, 0, 0, 1, 0, '
                                + (parseInt(event.targetTouches[0].pageX - Chats.startX) - delay)
                                +', 0, 0, 1)';
                        }
                    } else {
                        this.style.transform = '';
                        this.classList.remove('close');
                    }
                }, true);

                items[i].addEventListener('touchend', function(event) {
                    document.querySelector('#scroll_block').classList.remove('freeze');
                    this.classList.add('moving');

                    if (event.changedTouches[0]) {
                        moveX = parseInt(event.changedTouches[0].pageX - Chats.startX);

                        if (Math.abs(moveX) > this.offsetWidth/2 && Chats.slideAuthorized) {
                            Chats.closeItem(this, (moveX < 0));
                        }
                    }

                    this.style.transform = '';
                    this.classList.remove('close');
                    Chats.slideAuthorized = false;
                    Chats.startX = Chats.startY = 0;
                }, true);
            }

            if (clearAllActives) {
                items[i].classList.remove('active');
            }
            i++;
        }
    },
    closeItem(li, toLeft) {
        li.classList.add('moving');
        li.classList.add('closing');
        li.classList.add(toLeft ? 'to_left' : 'to_right');

        window.setTimeout(() => {
            Chats_ajaxClose(li.dataset.jid, (MovimUtils.urlParts().params[0] === li.dataset.jid));
        }, 500);
    },
    prepend: function(from, html) {
        MovimTpl.remove('#' + MovimUtils.cleanupId(from + '_chat_item'));
        MovimTpl.prepend('#chats_widget_list', html);
        Chats.refresh();
    }
};

MovimWebsocket.initiate(() => Chats_ajaxHttpGet());