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
        MovimUtils.textareaAutoheight(n);
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
    appendMucMessages : function(date, messages) {
        id = messages[0].jidfrom + '_conversation';
        var conversation = document.getElementById(id);
        datebox = Chat.room.cloneNode(true);
        datebox.innerHTML = date;

        if(conversation) {
            conversation.appendChild(datebox);
        }

        for(var i = 0, len = messages.length; i < len; ++i){
            bubble = Chat.room.cloneNode(true);


            if(messages[i].body.match(/^\/me/)) {
                bubble.querySelector('.message').className = 'message quote';
                messages[i].body = messages[i].body.substr(4);
            }

            bubble.querySelector('p.message').innerHTML = messages[i].body.replace(/\r\n?|\n/g, '<br />');
            bubble.querySelector('span.info').innerHTML = messages[i].publishedPrepared;
            bubble.querySelector('p.user').className = 'user ' + messages[i].color;

            bubble.querySelector('p.user').onclick = function(n) {
                var textarea = document.querySelector('#chat_textarea');
                textarea.value = this.innerHTML + ', ' + textarea.value;
                textarea.focus();
            };

            bubble.querySelector('p.user').innerHTML = messages[i].resource;
            if(conversation) {
                conversation.appendChild(bubble);
            }
        }
    },
    /*appendMessages : function(messages) {
        if(messages) {
            Chat.lastDate = null;
            Chat.date = messages[0].published;
            for(var i = 0, len = messages.length; i < len; ++i ) {
                Chat.appendMessage(messages[i], false);
            }
            Chat.edit = false;
            Chat.cleanBubbles();
        }
    },*/
    appendMessagesWrapper : function(page) {
        if(page) {
            //Chat.lastDate = null;
            //Chat.date = messages[0].published;
            for(date in page) {
                if (page[date].constructor == Array) { //groupchat
                    Chat.appendMucMessages(date, page[date]);
                } else {
                    for(speakertime in page[date]) {
                        Chat.appendSpeaker(speakertime, page[date][speakertime], false);
                    }
                }
            }
            Chat.edit = false;
            Chat.cleanBubbles();
        }
    },
    appendSpeaker : function(speakertime, data, prepend) {
        var bubble = null;
        var append = true;
        var offset = 0;
        var quickie = document.querySelector("[data-bubble='" + speakertime.substring(speakertime.indexOf('<')+1) + "']");
        if(quickie != null
            && quickie.parentNode == document.querySelector("#chat_widget .contained li:last-child")
            && !MovimUtils.hasClass(quickie, "sticker")
        ){
            bubble = quickie.parentNode;
            offset = bubble.querySelectorAll('div.bubble p').length;
            append = false;
        } else {
            var speaker = speakertime.substring(speakertime.indexOf('<') + 1, speakertime.indexOf('>'));

            if (data[0].session == data[0].jidfrom) {
                bubble = Chat.right.cloneNode(true);
                id = data[0].jidto + '_conversation';
            } else {
                bubble = Chat.left.cloneNode(true);
                id = data[0].jidfrom + '_conversation';
            }
            bubble.querySelector('div.bubble').setAttribute("data-bubble", speakertime.substring(speakertime.indexOf('<') + 1));
        }
        for(var i = 0, len = data.length; i < len; ++i) {
            if (data[i].body.match(/^\/me\s/)) {
                bubble.querySelector('div.bubble p').className = 'quote';
                data[i].body = data[i].body.substr(4);
            }

            if (data[i].sticker != null) {
                bubble.querySelector('div.bubble').className += ' sticker';
            }

            //if there is already a msg in this bubble, create another div
            if (bubble.querySelector('div.bubble p').innerHTML != "") {
                var div = document.createElement("div");
                var p = document.createElement("p");
                div.appendChild(p);
                var span = document.createElement("span");
                span.className = "info";
                div.appendChild(span);
                bubble.querySelector('div.bubble').appendChild(div);
            }

            if (data[i].id != null) {
                bubble.querySelectorAll('div.bubble > div')[i+offset].id = data[i].id;
                if (data[i].newid != null)
                    bubble.querySelectorAll('div.bubble > div')[i+offset].id = data[i].newid;
            }

            if (data[i].sticker != null) {
                bubble.querySelectorAll('div.bubble  p')[i+offset].innerHTML =
                    '<img src="' + data[i].sticker.url +
                    '" width="' + data[i].sticker.width +
                    '" height="' + data[i].sticker.height + '"/>';
            } else {
                bubble.querySelectorAll('div.bubble  p')[i+offset].innerHTML = data[i].body.replace(/\r\n?|\n/g, '<br />');
            }

            var info = bubble.querySelectorAll('div.bubble span.info')[i+offset];
            info.innerHTML = data[i].publishedPrepared;

            if (data[i].edited) {
                info.innerHTML = '<i class="zmdi zmdi-edit"></i> ' + info.innerHTML;
            }

            if (data[i].delivered) {
                info.innerHTML = '<i class="zmdi zmdi-check" title="' + data[i].delivered + '"></i> ' + info.innerHTML;
            }

            if (data[i].edited || data[i].delivered) {
                var elem = document.getElementById(data[i].id);
                if (elem) {
                    elem.parentElement.replaceChild(bubble.querySelectorAll('div.bubble > div')[i+offset], elem);
                    append = false;
                }
            }
        }

        /*if(prepend) {
         Chat.date = data[i].published;
         var discussion = document.querySelector('#chat_widget div.contained');
         // We prepend
         movim_prepend(id, bubble.outerHTML);

         // And we scroll where we were
         var scrollDiff = discussion.scrollHeight - Chat.lastScroll;
         discussion.scrollTop += scrollDiff;
         Chat.lastScroll = discussion.scrollHeight;
         } */
        if (append)
            movim_append(id, bubble.outerHTML);
        if(bubble.className.indexOf('oppose') > -1 && prepend !== true)
            MovimTpl.scrollPanel();
    },
    appendMessage : function(message, prepend) {
        if(message.body == '') return;

        var bubble = null;
        var id = null;

        var scrolled = MovimTpl.isPanelScrolled();

        if(Chat.left != null) {
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
                bubble.querySelector('div.bubble > p').className = 'quote';
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
                    info.innerHTML = '<i class="zmdi zmdi-check" title="' + message.delivered + '"></i> ' + info.innerHTML;
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
    },
    toggleAction: function(l) {
        var send_button = document.querySelector(".chat_box span[data-jid]");
        var attachment_button = document.querySelector(".chat_box span.control:not([data-jid])");
        if(l > 0){
            MovimUtils.showElement(send_button);
            MovimUtils.hideElement(attachment_button);
        } else {
            MovimUtils.showElement(attachment_button);
            MovimUtils.hideElement(send_button);
        }
    }
};

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
        MovimUtils.textareaAutoheight(textarea);
    });
}

document.addEventListener('focus', function() {
    var textarea = document.querySelector('#chat_textarea');
    if(textarea) textarea.focus();
});

var state = 0;
