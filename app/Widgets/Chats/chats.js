var Chats = {
    startX: 0,
    startY: 0,
    translateX: 0,
    translateY: 0,
    slideAuthorized: false,

    setFilter: function (filter) {
        document.querySelector('#chats_widget_header').dataset.filter = filter;
        Chats_ajaxSetFilter(filter);
    },

    refreshFilters: function () {
        var filter = document.querySelector('#chats_widget_header').dataset.filter;

        document.querySelectorAll('#chats_widget_header span.chip').forEach(chip => {
            chip.classList.remove('enabled');

            if (chip.dataset.filter == filter) chip.classList.add('enabled');
        });
    },

    refresh: function () {
        var list = document.querySelector('#chats');
        if (!list) return;

        Chats.refreshFilters();

        var trim = list.innerHTML.trim();

        if (trim === '') list.innerHTML = trim;

        var items = document.querySelectorAll('ul#chats li:not(.subheader)');
        var i = 0;

        while (i < items.length) {
            if (items[i].querySelector('img.tinythumb')) {
                var img = items[i].querySelector('img.tinythumb');

                try {
                    img.src = thumbHashToDataURL(MovimUtils.base64ToBinary(img.dataset.thumbhash));
                } catch (error) {
                    console.log('Cannot handle thumbhash hash');
                }
            }

            if (items[i].dataset.jid != null) {
                items[i].onclick = function (e) {
                    Rooms.refresh();
                    MovimUtils.addClass('ul#bottomnavigation', 'hidden');

                    Chat.get(this.dataset.jid);
                };

                items[i].onmousedown = function (e) {
                    if (e.which == 2) {
                        Chats.closeItem(this, false);
                        e.preventDefault();
                    }
                }

                clientWidth = Math.abs(document.body.clientWidth);
                delay = 20;

                if (MovimUtils.isMobile()) {
                    items[i].addEventListener('touchstart', function (event) {
                        if (MovimTpl.menuDragged) return;

                        Chats.startX = event.targetTouches[0].pageX;
                        Chats.startY = event.targetTouches[0].pageY;

                        this.classList.remove('moving');
                    }, true);

                    items[i].addEventListener('touchmove', function (event) {
                        if (MovimTpl.menuDragged) return;

                        Chats.translateX = parseInt(event.targetTouches[0].pageX - Chats.startX);
                        Chats.translateY = parseInt(event.targetTouches[0].pageY - Chats.startY);

                        if (Math.abs(Chats.translateX) > delay && Math.abs(Chats.translateX) <= clientWidth) {
                            if (Math.abs(Chats.translateX) > this.offsetWidth / 2) {
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

                    items[i].addEventListener('touchend', function (event) {
                        if (MovimTpl.menuDragged) return;

                        this.classList.add('moving');

                        if (Math.abs(Chats.translateX) > this.offsetWidth / 2 && Chats.slideAuthorized) {
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

            i++;
        }
    },

    clearAllActives: function () {
        document.querySelectorAll('ul#chats li:not(.subheader)')
            .forEach(item => item.classList.remove('active'));
    },

    setActive: function (jid) {
        Chats.clearAllActives();
        Rooms.clearAllActives();
        MovimUtils.addClass('ul#chats li[data-jid="' + jid + '"]', 'active');
    },

    closeItem(li, toLeft) {
        li.classList.add('closing');
        li.classList.add(toLeft ? 'to_left' : 'to_right');

        window.setTimeout(() => {
            Chats_ajaxClose(li.dataset.jid, (MovimUtils.urlParts().params[0] === li.dataset.jid));
        }, 400);
    }
};

MovimEvents.registerWindow('loaded', 'chats', () => Chats.refresh());

MovimWebsocket.initiate(() => Chats_ajaxHttpGet());
