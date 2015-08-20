var Chat = {
    left : null,
    right: null,
    room: null,
    previous: null,
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
        Chat_ajaxSendMessage(jid, encodeURIComponent(text), muc);
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
        Chat.left = div.firstChild;
        div.innerHTML = right;
        Chat.right = div.firstChild;
        div.innerHTML = room;
        Chat.room = div.firstChild;
    },
    appendMessages : function(messages) {
        if(messages) {
	    for(var i = 0, len = messages.length; i < len; ++i ) {
                Chat.appendMessage(messages[i]);
            }
	}
    },
    appendMessage : function(message) {
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

            bubble.querySelector('div').innerHTML = message.body;
            bubble.querySelector('span.info').innerHTML = message.published;
            bubble.querySelector('span.user').className = 'user ' + message.color;

            bubble.querySelector('span.user').onclick = function(n) {
                var textarea = document.querySelector('#chat_textarea');
                textarea.value = this.innerHTML + ', ' + textarea.value;
                textarea.focus();
            };

            bubble.querySelector('span.user').innerHTML = message.resource;

            var conversation = document.getElementById(id);
            if(conversation) {
		conversation.appendChild(bubble);
	    }

	    bubble.querySelector('div').className = '';
        } else {
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
                bubble.querySelector('div.bubble div').innerHTML = message.body;
                bubble.querySelector('div.bubble span.info').innerHTML = message.published;

                movim_append(id, bubble.outerHTML);
                bubble.querySelector('div.bubble').className = 'bubble';
            }
        }

        if(scrolled) MovimTpl.scrollPanel();
    }
}

MovimWebsocket.attach(function() {
    var jid = document.querySelector('#chat_widget').dataset.jid;
    if(jid) {
        Chat_ajaxGet(jid);
    }
});

Upload.attach(function() {
    var textarea = document.querySelector('#chat_textarea');
    textarea.value = Upload.get + ' ' + textarea.value;
    textarea.focus();
    movim_textarea_autoheight(textarea);
});

var state = 0;
