var Rooms = {
    default_services: [],

    setDefaultServices: function(services) {
        Rooms.default_services = services;
    },

    toggleEdit: function(){
        document.querySelector('#rooms_widget ul.list.rooms').classList.toggle('edition');
    },

    checkNoConnected: function() {
        if (!document.querySelector('#rooms_widget ul.list.rooms li.connected')) {
            Rooms.toggleShowAll();
        }
    },

    toggleShowAll: function(){
        document.querySelector('#rooms_widget ul.list.rooms').classList.toggle('all');
    },

    selectGatewayRoom : function(room, name) {
        document.querySelector('form[name="bookmarkmucadd"] input[name=jid]').value = room;
        document.querySelector('form[name="bookmarkmucadd"] input[name=name]').value = name;
    },

    setJid: function(slugifiedJid) {
        let input = document.querySelector('form[name=bookmarkmucadd] input[name=jid]');

        if (input && input.value === '') {
            input.value = slugifiedJid;
        }
    },

    suggest: function() {
        let input = document.querySelector('form[name=bookmarkmucadd] input[name=jid]');

        if (input && input.value != '' && !input.value.includes('@')) {
            let suggestions = document.querySelector('datalist#suggestions');
            suggestions.textContent = '';

            Rooms.default_services.forEach(function(item) {
               var option = document.createElement('option');
               option.value = input.value + '@' + item.server;
               suggestions.appendChild(option);
            });
        }
    },

    refresh: function() {
        var items = document.querySelectorAll('#rooms_widget ul.list.rooms li:not(.subheader)');
        var i = 0;
        while(i < items.length)
        {
            if (items[i].dataset.jid != null) {
                items[i].onclick = function(e) {
                    Chats.refresh(true);

                    items.forEach(item => item.classList.remove('active'));
                    this.classList.add('active');

                    Chat.getRoom(this.dataset.jid);
                }
            }

            items[i].classList.remove('active');

            i++;
        }
    },

    clearRooms: function() {
        document.querySelector('#rooms_widget ul.list.rooms').innerHTML = '';
    },

    setRoom: function(id, html) {
        var listSelector = '#rooms_widget ul.list.rooms ';
        var list = document.querySelector(listSelector);
        var element = list.querySelector('#' + id);

        if (element) element.remove();

        var rooms = document.querySelectorAll(listSelector + '> li');
        var i = 0;

        while(i < rooms.length)
        {
            if (rooms[i].id > id) {
                MovimTpl.prependBefore(listSelector + '#' + rooms[i].id, html);
                break;
            }

            i++;
        }

        if (i == rooms.length) {
            MovimTpl.append(listSelector, html);
        }

        Rooms.refresh();
    },

    setUnread: function(id, unread) {
        var element = document.querySelector('#rooms_widget ul.list.rooms #' + id);

        if (unread) {
            element.classList.add('unread');
        } else {
            element.classList.remove('unread');
        }
    }
}

MovimWebsocket.initiate(() => Rooms_ajaxHttpGet());
