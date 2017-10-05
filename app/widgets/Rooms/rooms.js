var Rooms = {
    anonymous_room: false,

    refresh: function() {
        var items = document.querySelectorAll('#rooms_widget ul li:not(.subheader)');
        var i = 0;
        while(i < items.length)
        {
            if(items[i].dataset.jid != null) {
                items[i].onclick = function(e) {
                    Chats.refresh();

                    items.forEach(item => item.classList.remove('active'));
                    this.classList.add('active');

                    Chat_ajaxGetRoom(this.dataset.jid);
                }
            }

            MovimUtils.removeClass(items[i], 'active');

            i++;
        }

        Notification_ajaxGet();
    },

    /**
     * @brief Connect to an anonymous server
     * @param The jid to remember
     */
    anonymousInit : function() {
        MovimWebsocket.register(function()
        {
            form = document.querySelector('form[name="loginanonymous"]');
            form.onsubmit = function(e) {
                e.preventDefault();
                // We login
                LoginAnonymous_ajaxLogin(this.querySelector('input#nick').value);
            }
        });
    },

    /**
     * @brief Join an anonymous room
     * @param The jid to remember
     */
    anonymousJoin : function() {
        // And finally we join
        Rooms_ajaxExit(Rooms.anonymous_room);
        Rooms_ajaxJoin(Rooms.anonymous_room);

        // We display the room
        Chat_ajaxGetRoom(Rooms.anonymous_room);
    }
}

MovimWebsocket.attach(function() {
    Rooms.anonymousInit();
    Rooms_ajaxDisplay();
});
