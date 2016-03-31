var Chat = {
    left : null,
    right: null,
    room: null,
    date: null,
    lastScroll: null,
    lastDate: null,
    edit: false,
    sendMessage: function(jid, muc)
    {
        var n = document.querySelector('#chat_textarea');
        var text = n.value;
        n.value = "";
        n.focus();
        movim_textarea_autoheight(n);
        if(Chat.edit) {
            Chat.edit = false;
            Chat_ajaxCorrect(jid, encodeURIComponent(text));
        } else {
            Chat_ajaxSendMessage(jid, encodeURIComponent(text), muc);
        }
    },
    focus: function()
    {
        if(document.documentElement.clientWidth > 1024) {
            document.querySelector('#chat_textarea').focus();
        }
    },
    setTextarea: function(value)
    {
        Chat.edit = true;
        document.querySelector('#chat_textarea').value = value;
    },
    clearReplace: function()
    {
        Chat.edit = false;
        document.querySelector('#chat_textarea').value = '';
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
        if(discussion.dataset.muc != true) {
            discussion.onscroll = function() {
                if(this.scrollTop < 1) {
                    var chat = document.querySelector('#chat_widget');
                    Chat.lastScroll = this.scrollHeight;
                    Chat_ajaxGetHistory(chat.dataset.jid, Chat.date);
                }
            };
        }
    },
    appendMessages : function(messages) {
        if(messages) {
            Chat.lastDate = null;
            Chat.date = messages[0].published;
            for(var i = 0, len = messages.length; i < len; ++i ) {
                Chat.appendMessage(messages[i], false);
            }
            Chat.edit = false;
            Chat.cleanBubbles();
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

            if(message.body.match(/^\/me/)
            && bubble.querySelector('div') != null) {
                bubble.querySelector('div').className = 'quote';
                message.body = message.body.substr(4);
            }

            bubble.querySelector('p.message').innerHTML = message.body.replace(/\r\n?|\n/g, '<br />');
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
        } else if(Chat.left != null) {
            if(message.session == message.jidfrom) {
                bubble = Chat.right.cloneNode(true);
                id = message.jidto + '_conversation';
            } else {
                bubble = Chat.left.cloneNode(true);
                id = message.jidfrom + '_conversation';
            }

            if(message.id != null) {
                bubble.id = message.id;
                if(message.newid != null)
                    bubble.id = message.newid;
            }

            if(message.body.match(/^\/me\s/)) {
                bubble.querySelector('div.bubble').className = 'bubble quote';
                message.body = message.body.substr(4);
            }

            if(message.sticker != null) {
                bubble.querySelector('div.bubble').className += ' sticker';
            }

            if(bubble) {
                if(message.sticker != null) {
                    bubble.querySelector('div.bubble > p').innerHTML =
                        '<img src="' + message.sticker.url +
                        '" width="' + message.sticker.width +
                        '" height="' + message.sticker.height + '"/>';
                } else {
                    bubble.querySelector('div.bubble > p').innerHTML = message.body.replace(/\r\n?|\n/g, '<br />');
                }

                var info = bubble.querySelector('div.bubble > span.info');
                info.innerHTML = message.publishedPrepared;

                if(message.edited) {
                    info.innerHTML = '<i class="zmdi zmdi-edit"></i> ' + info.innerHTML;
                }

                if(message.delivered) {
                    info.innerHTML = '<i class="zmdi zmdi-check"></i> ' + info.innerHTML;
                }

                if(prepend) {
                    Chat.date = message.published;
                    var discussion = document.querySelector('#chat_widget div.contained');

                    // We prepend
                    movim_prepend(id, bubble.outerHTML);

                    // And we scroll where we were
                    var scrollDiff = discussion.scrollHeight - Chat.lastScroll;
                    discussion.scrollTop += scrollDiff;
                    Chat.lastScroll = discussion.scrollHeight;
                } else if(message.edited
                       || message.delivered) {
                    var elem = document.getElementById(message.id);
                    if(elem)
                        elem.parentElement.replaceChild(bubble, elem);
                    else
                        movim_append(id, bubble.outerHTML);
                } else {
                    movim_append(id, bubble.outerHTML);
                }

                //bubble.querySelector('div.bubble').className = 'bubble';

                if(bubble.className.indexOf('oppose') > -1
                && prepend == null) MovimTpl.scrollPanel();
            }
        }

        if(scrolled && prepend == null) MovimTpl.scrollPanel();
    },
    cleanBubbles : function() {
        var bubbles = document.querySelectorAll('#chat_widget .contained ul.list > li');
        var previous = null;

        for(var i = 0, len = bubbles.length; i < len; ++i ) {
            bubbles[i].className = bubbles[i].className.replace(' same', '');

            if(bubbles[i].className.indexOf('oppose') > -1) {
                if(previous == 'right') {
                    bubbles[i].className += ' same';
                }

                previous = 'right';
            } else {
                if(previous == 'left') {
                    bubbles[i].className += ' same';
                }

                previous = 'left';
            }

            /*if(bubbles[i].className.indexOf('room') > -1) {
                var lastDate = bubbles[i].querySelector('span.info').innerHTML;
                if(lastDate == Chat.lastDate) {
                    bubbles[i].querySelector('span.info').innerHTML = '';
                }

                Chat.lastDate = lastDate;
            }*/
        }
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

if(typeof Upload != 'undefined') {
    Upload.attach(function() {
        var textarea = document.querySelector('#chat_textarea');
        textarea.value = Upload.get + ' ' + textarea.value;
        textarea.focus();
        movim_textarea_autoheight(textarea);
    });
}

document.addEventListener('focus', function() {
    var textarea = document.querySelector('#chat_textarea');
    if(textarea) textarea.focus();
});

var state = 0;
