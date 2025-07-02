var Rooms = {
    default_services: [],

    setDefaultServices: function (services) {
        Rooms.default_services = services;
    },

    toggleEdit: function () {
        document.querySelector('#rooms ul.list.rooms').classList.toggle('edition');
    },

    toggleScroll: function () {
        var chats = document.querySelector('#chats_widget_header');
        var rooms = document.querySelector('#rooms');

        if (rooms.dataset.scroll) {
            chats.scrollIntoView();
        } else {
            rooms.scrollIntoView();
        }
    },

    scrollToChats: function () {
        var chats = document.querySelector('#chats_widget_header');
        var rooms = document.querySelector('#rooms');

        var chatcounter = document.querySelector('#chatcounter i');
        var bottomchatcounter = document.querySelector('#bottomchatcounter i');

        chats.dataset.scroll = true;
        delete rooms.dataset.scroll;

        chatcounter.innerHTML = bottomchatcounter.innerHTML = 'chat_bubble';
    },

    scrollToRooms: function () {
        var chats = document.querySelector('#chats_widget_header');
        var rooms = document.querySelector('#rooms');

        var chatcounter = document.querySelector('#chatcounter i');
        var bottomchatcounter = document.querySelector('#bottomchatcounter i');

        delete chats.dataset.scroll;
        rooms.dataset.scroll = true;

        chatcounter.innerHTML = bottomchatcounter.innerHTML = 'forum';
    },

    checkNoConnected: function () {
        if (
            !document.querySelector('#rooms ul.list.rooms li.connected')
            && localStorage.getItem('rooms_all') == 'true'
        ) {
            document.querySelector('#rooms ul.list.rooms').classList.add('all');
        }
    },

    toggleShowAll: function () {
        document.querySelector('#rooms ul.list.rooms').classList.toggle('all');
        localStorage.setItem('rooms_all', document.querySelector('#rooms ul.list.rooms').classList.contains('all'));

        Rooms.displayToggleButton();
    },

    displayToggleButton: function () {
        document.querySelectorAll('#rooms span.chip').forEach(chip => {
            chip.classList.remove('enabled');
        });

        if (localStorage.getItem('rooms_all') == 'true') {
            document.querySelector('#rooms span.chip[data-filter=all]').classList.add('enabled');
        } else {
            document.querySelector('#rooms span.chip[data-filter=connected]').classList.add('enabled');
        }
    },

    selectGatewayRoom: function (room, name) {
        document.querySelector('form[name="bookmarkmucadd"] input[name=jid]').value = room;
        document.querySelector('form[name="bookmarkmucadd"] input[name=name]').value = name;
    },

    setJid: function (slugifiedJid) {
        let input = document.querySelector('form[name=bookmarkmucadd] input[name=jid]');

        if (input && input.value === '') {
            input.value = slugifiedJid;
        }
    },

    suggest: function () {
        let input = document.querySelector('form[name=bookmarkmucadd] input[name=jid]');

        if (input && input.value != '' && !input.value.includes('@')) {
            let suggestions = document.querySelector('datalist#suggestions');
            if (suggestions) {
                suggestions.textContent = '';

                Rooms.default_services.forEach(function (item) {
                    var option = document.createElement('option');
                    option.value = input.value + '@' + item.server;
                    suggestions.appendChild(option);
                });
            }
        }
    },

    refresh: function (callSecond) {
        Rooms.displayToggleButton();

        var parent = document.querySelector('#rooms').parentElement;

        parent.onscroll = e => {
            if (e.target.scrollTop + 5 >= document.querySelector('#rooms').offsetTop) {
                Rooms.scrollToRooms();
            } else {
                Rooms.scrollToChats();
            }
        };

        var list = document.querySelector('#rooms ul.list.rooms');
        var items = document.querySelectorAll('#rooms ul.list.rooms li:not(.subheader)');
        var i = 0;

        var differentStates = false;
        list.classList.remove('different_states');

        while (i < items.length) {
            if (items[i].dataset.jid != null) {
                items[i].onclick = function (e) {
                    Chat.getRoom(this.dataset.jid);
                }
            }

            // If we have a room with a call we do a second daemon refresh to get the live status
            if (items[i].classList.contains('muc_call') && callSecond == true) {
                Rooms_ajaxSecondGet(items[i].dataset.jid);
            }

            if (
                i >= 1
                && !differentStates
                && items[i - 1].classList.contains('connected') != items[i].classList.contains('connected')
            ) {
                differentStates = true;
            }

            items[i].classList.remove('active');

            i++;
        }

        if (differentStates) {
            list.classList.add('different_states');
        } else {
            Rooms.checkNoConnected();
        }
    },

    clearRooms: function () {
        document.querySelector('#rooms ul.list.rooms').innerHTML = '';
    },

    setRoom: function (id, html, noSecondRefresh) {
        var listSelector = '#rooms ul.list.rooms ';
        var list = document.querySelector(listSelector);
        var element = list.querySelector('#' + id);

        if (element) element.remove();

        var rooms = document.querySelectorAll(listSelector + '> li');
        var i = 0;

        while (i < rooms.length) {
            if (rooms[i].id > id) {
                MovimTpl.prependBefore(listSelector + '#' + rooms[i].id, html);
                break;
            }

            i++;
        }

        if (i == rooms.length) {
            MovimTpl.append(listSelector, html);
        }

        Rooms.refresh(noSecondRefresh);
    },

    clearAllActives: function () {
        document.querySelectorAll('#rooms ul.list.rooms li:not(.subheader)')
            .forEach(item => item.classList.remove('active'));
    },

    setActive: function (jid) {
        Chats.clearAllActives();
        Rooms.clearAllActives();
        MovimUtils.addClass('#rooms ul.list.rooms li[data-jid="' + jid + '"]', 'active');
    },

    setUnread: function (id, unread) {
        var element = document.querySelector('#rooms ul.list.rooms #' + id);

        if (element) {
            if (unread) {
                element.classList.add('unread');
            } else {
                element.classList.remove('unread');
            }
        }
    }
}

MovimWebsocket.initiate(() => {
    Rooms_ajaxHttpGet()
    Rooms.checkNoConnected();
});
