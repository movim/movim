var Chat = {
    left : null,
    right: null,
    room: null,
    previous: null,
    date: null,
    lastScroll: null,
    addSmiley: function(element) {
        var n = document.querySelector('#chat_textarea');
        n.value = n.value + element.dataset.emoji;
        n.focus();
        Dialog.clear();
    },
    sendMessage: function(jid, muc)
    {
        var n = document.querySelector('#chat_textarea');
        var text = n.value;
        n.value = "";
        n.focus();
        movim_textarea_autoheight(n);
        Chat_ajaxSendMessage(jid, encodeURIComponent(text), muc);
    },
    focus: function()
    {
        if(document.documentElement.clientWidth > 1024) {
            document.querySelector('#chat_textarea').focus();
        }
    },
    appendTextarea: function(value)
    {
    },
    notify : function(title, body, image)
    {
        if(document_focus == false) {
            movim_title_inc();
            movim_desktop_notification(title, body, image);
        }
    },
    empty : function()
    {
        Chat_ajaxGet();
    },
    setBubbles : function(left, right, room) {
        var div = document.createElement('div');

        div.innerHTML = left;
        Chat.left = div.firstChild.cloneNode(true);
        div.innerHTML = right;
        Chat.right = div.firstChild.cloneNode(true);
        div.innerHTML = room;
        Chat.room = div.firstChild.cloneNode(true);

        Chat.setScrollBehaviour();
    },
    setScrollBehaviour : function() {
        var discussion = document.querySelector('#chat_widget div.contained');
        discussion.onscroll = function() {
            if(this.scrollTop < 1) {
                var chat = document.querySelector('#chat_widget');
                Chat.lastScroll = this.scrollHeight;
                Chat_ajaxGetHistory(chat.dataset.jid, Chat.date);
            }
        };
    },
    appendMessages : function(messages) {
        if(messages) {
            Chat.date = messages[0].published;
            for(var i = 0, len = messages.length; i < len; ++i ) {
                Chat.appendMessage(messages[i], false);
            }
        }
    },
    appendMessage : function(message, prepend) {
        if(message.body == '') return;

        var bubble = null;
        var id = null;

        var scrolled = MovimTpl.isPanelScrolled();

        if(message.type == 'groupchat') {
            bubble = Chat.room.cloneNode(true);

            id = message.jidfrom + '_conversation';

            if(message.body.match(/^\/me/)) {
                bubble.querySelector('div').className = 'quote';
                message.body = message.body.substr(4);
            }

            bubble.querySelector('p.message').innerHTML = message.body;
            bubble.querySelector('span.info').innerHTML = message.publishedPrepared;
            bubble.querySelector('p.user').className = 'user ' + message.color;

            bubble.querySelector('p.user').onclick = function(n) {
                var textarea = document.querySelector('#chat_textarea');
                textarea.value = this.innerHTML + ', ' + textarea.value;
                textarea.focus();
            };

            bubble.querySelector('p.user').innerHTML = message.resource;
            var conversation = document.getElementById(id);
            if(conversation) {
                conversation.appendChild(bubble);
            }

            //bubble.querySelector('p.message').className = '';
        } else if(Chat.left != null) {
            if(message.session == message.jidfrom) {
                bubble = Chat.right.cloneNode(true);
                if(Chat.previous == 'right') {
                    bubble.className += ' same';
                }

                Chat.previous = 'right';
                id = message.jidto + '_conversation';
            } else {
                bubble = Chat.left.cloneNode(true);
                if(Chat.previous == 'left') {
                    bubble.className += ' same';
                }

                Chat.previous = 'left';
                id = message.jidfrom + '_conversation';
            }

            if(message.body.match(/^\/me/)) {
                bubble.querySelector('div.bubble').className = 'bubble quote';
                message.body = message.body.substr(4);
            }

            if(bubble) {
                bubble.querySelector('div.bubble > p').innerHTML = message.body;

                bubble.querySelector('div.bubble > span.info').innerHTML = message.publishedPrepared;

                if(prepend) {
                    Chat.date = message.published;
                    var discussion = document.querySelector('#chat_widget div.contained');

                    // We prepend
                    movim_prepend(id, bubble.outerHTML);

                    // And we scroll where we were
                    var scrollDiff = discussion.scrollHeight - Chat.lastScroll;
                    discussion.scrollTop += scrollDiff;
                    Chat.lastScroll = discussion.scrollHeight;
                } else {
                    movim_append(id, bubble.outerHTML);
                }

                bubble.querySelector('div.bubble').className = 'bubble';

                if(bubble.className.indexOf('oppose') > -1
                && prepend == null) MovimTpl.scrollPanel();
            }
        }

        if(scrolled && prepend == null) MovimTpl.scrollPanel();
    }
}

MovimWebsocket.attach(function() {
    var chat = document.querySelector('#chat_widget');
    var jid = chat.dataset.jid;
    if(jid) {
        MovimTpl.showPanel();
        Chat_ajaxGet(jid);
        Notification.current('chat|' + jid);
    }
});

Upload.attach(function() {
    var textarea = document.querySelector('#chat_textarea');
    textarea.value = Upload.get + ' ' + textarea.value;
    textarea.focus();
    movim_textarea_autoheight(textarea);
});

var state = 0;
