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

        /*if(conversation) {
            conversation.appendChild(datebox);
        }*/

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
    appendMessagesWrapper : function(page, prepend) {
        if(page) {
            var scrolled = MovimTpl.isPanelScrolled();
            Chat.lastDate = null;
            for(date in page) {
                if (page[date].constructor == Array) { //groupchat
                    if(!Chat.date)
                        Chat.date = page[date][0].published;
                    Chat.appendMucMessages(date, page[date]);
                } else {
                    for(speakertime in page[date]) {
                        if(!Chat.date)
                            Chat.date = page[date][speakertime][0].published;
                        Chat.appendSpeaker(speakertime, page[date][speakertime], prepend);
                    }
                }
            }
            // Only scroll down if scroll was at the bottom before the new msg
            // => don't scroll if the user was reading previous messages
            if(scrolled && prepend !== true)
                MovimTpl.scrollPanel();
        }
    },
    appendSpeaker : function(idjidtime, data, prepend) {
        var bubble = null,
            mergeMsg = false,
            msgStack,
            refBubble;
        var jidtime = idjidtime.substring(idjidtime.indexOf('<') + 1);

        if(prepend) {
            refBubble = document.querySelector("#chat_widget .contained li:first-child");
            msgStack = document.querySelector("[data-bubble='" + jidtime + "']");
        } else {
            refBubble = document.querySelector("#chat_widget .contained li:last-child");
            var stack = document.querySelectorAll("[data-bubble='" + jidtime + "']");
            msgStack = stack[stack.length-1];
        }
        if(msgStack != null
            && msgStack.parentNode == refBubble
            && idjidtime.indexOf("sticker<") == -1
            && !MovimUtils.hasClass(msgStack, "sticker")
        ){
            bubble = msgStack.parentNode;
            mergeMsg = true;
        } else {
            if (data[0].session == data[0].jidfrom) {
                bubble = Chat.right.cloneNode(true);
                id = data[0].jidto + '_conversation';
            } else {
                bubble = Chat.left.cloneNode(true);
                id = data[0].jidfrom + '_conversation';
            }
            bubble.querySelector('div.bubble').setAttribute("data-bubble", jidtime);
            bubble.querySelector('div.bubble').setAttribute("data-publishedPrepared", data[0].publishedPrepared);
        }

        var msg = bubble.querySelector('div.bubble > div');
        var span = msg.getElementsByTagName('span')[0];
        var p = msg.getElementsByTagName('p')[0];
        for(var i = 0, len = data.length; i < len; ++i) {
            // If there is already a msg in this bubble, create another div (next msg or replacement)
            if (bubble.querySelector('div.bubble p')
            && bubble.querySelector('div.bubble p').innerHTML != "") {
                msg = document.createElement("div");
                p = document.createElement("p");
                span = document.createElement("span");
                span.className = "info";
            }

            if (data[i].body.match(/^\/me\s/)) {
                p.className = 'quote';
                // Remove "/me " from beginning of body
                data[i].body = data[i].body.substr(4);
            }
            if (data[i].id != null) {
                msg.setAttribute("id", data[i].id);
                if (data[i].newid != null)
                    msg.setAttribute("id", data[i].newid);
            }

            if (data[i].sticker != null) {
                MovimUtils.addClass(bubble.querySelector('div.bubble'), 'sticker');
                p.appendChild(Chat.getStickerHtml(data[i].sticker));
            } else {
                p.innerHTML = data[i].body.replace(/\r\n?|\n/g, '<br />');
            }


            if (data[i].edited) {
                span.appendChild(Chat.getEditedIcoHtml());
            }
            if (data[i].delivered) {
                span.appendChild(Chat.getDeliveredIcoHtml(data[i].delivered));
            }

            msg.appendChild(p);
            msg.appendChild(span);

            var elem = document.getElementById(data[i].id);
            if (elem) {
                elem.parentElement.replaceChild(msg, elem);
                mergeMsg = true;
            } else {
                if(prepend)
                    bubble.querySelector('div.bubble').insertBefore( msg, bubble.querySelector('div.bubble').firstChild );
                else
                    bubble.querySelector('div.bubble').appendChild(msg);
            }
        }

        if(prepend){
            Chat.date = data[0].published;
            var discussion = document.querySelector('#chat_widget div.contained');
            // We prepend
            if (!mergeMsg)
                movim_prepend(id, bubble.outerHTML);

            // And we scroll where we were
            var scrollDiff = discussion.scrollHeight - Chat.lastScroll;
            discussion.scrollTop += scrollDiff;
            Chat.lastScroll = discussion.scrollHeight;
        } else {
            if (!mergeMsg) {
                movim_append(id, bubble.outerHTML);
            }
        }
    },
    getStickerHtml: function(sticker) {
        var img = document.createElement("img");
        img.setAttribute("src", sticker.url);
        img.setAttribute("width", sticker.width);
        img.setAttribute("height", sticker.height);
        return img;
    },
    getEditedIcoHtml: function() {
        var i = document.createElement("i");
        i.setAttribute("class", "zmdi zmdi-edit");
        return i;
    },
    getDeliveredIcoHtml: function(delivered) {
        var i = document.createElement("i");
        i.setAttribute("class", "zmdi zmdi-check");
        i.setAttribute("title", delivered);
        return i;
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
