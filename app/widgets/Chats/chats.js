var Chats = {
    startX: 0,
    startY: 0,
    translateX: 0,
    translateY: 0,
    slideAuthorized: false,

    setActive: function(jid) {
        MovimUtils.addClass('#' + MovimUtils.cleanupId(jid + '_chat_item'), 'active');
    },

    refresh: function(clearAllActives) {
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

                    Chat.get(this.dataset.jid);

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
                delay = 20;

                if (MovimUtils.isMobile())  {
                    items[i].addEventListener('touchstart', function(event) {
                        if (MovimTpl.menuDragged) return;

                        Chats.startX = event.targetTouches[0].pageX;
                        Chats.startY = event.targetTouches[0].pageY;

                        this.classList.remove('moving');
                    }, true);

                    items[i].addEventListener('touchmove', function(event) {
                        if (MovimTpl.menuDragged) return;

                        Chats.translateX = parseInt(event.targetTouches[0].pageX - Chats.startX);
                        Chats.translateY = parseInt(event.targetTouches[0].pageY - Chats.startY);

                        if (Math.abs(Chats.translateX) > delay && Math.abs(Chats.translateX) <= clientWidth) {
                            if (Math.abs(Chats.translateX) > this.offsetWidth/2) {
                                this.classList.add('close');
                            } else {
                                this.classList.remove('close');
                            }

                            if (Math.abs(Chats.translateY) < delay) {
                                Chats.slideAuthorized = true;
                            }

                            var moveX = parseInt(event.targetTouches[0].pageX - Chats.startX);
                            moveX = moveX < 0 ? (moveX + delay) : (moveX - delay);

                            if (Chats.slideAuthorized) {
                                this.style.transform = 'translateX(' + (moveX) + 'px)';
                            }
                        } else {
                            this.style.transform = '';
                            this.classList.remove('close');
                        }
                    }, true);

                    items[i].addEventListener('touchend', function(event) {
                        if (MovimTpl.menuDragged) return;

                        this.classList.add('moving');

                        if (Math.abs(Chats.translateX) > this.offsetWidth/2 && Chats.slideAuthorized) {
                            Chats.closeItem(this, (Chats.translateX < 0));
                        } else {
                            this.classList.remove('close');
                        }

                        this.style.transform = '';
                        Chats.slideAuthorized = false;
                        Chats.startX = Chats.startY = Chats.translateX = Chats.translateY = 0;
                    }, true);
                }
            }

            if (clearAllActives) {
                items[i].classList.remove('active');
            }
            i++;
        }
    },
    closeItem(li, toLeft) {
        li.classList.add('closing');
        li.classList.add(toLeft ? 'to_left' : 'to_right');

        window.setTimeout(() => {
            Chats_ajaxClose(li.dataset.jid, (MovimUtils.urlParts().params[0] === li.dataset.jid));
        }, 400);
    },
    prepend: function(from, html) {
        MovimTpl.remove('#' + MovimUtils.cleanupId(from + '_chat_item'));
        MovimTpl.prepend('#chats_widget_list', html);
        Chats.refresh();
    }
};

movimAddOnload(() => Chats.refresh());
MovimWebsocket.initiate(() => Chats_ajaxHttpGet());
