var Chat = {
    left : null,
    right: null,
    room: null,
    date: null,
    lastScroll: null,
    lastHeight: null,
    edit: false,

    // Chat state
    state: null,
    since: null,

    sendMessage: function(jid, muc)
    {
        var n = document.querySelector('#chat_textarea');
        var text = n.value;
        n.value = "";
        n.focus();
        MovimUtils.textareaAutoheight(n);

        localStorage.removeItem(n.dataset.jid + '_message');

        if(Chat.edit) {
            Chat.edit = false;
            Chat_ajaxCorrect(jid, encodeURIComponent(text));
        } else {
            Chat_ajaxSendMessage(jid, encodeURIComponent(text), muc);
        }
    },

    focus: function(jid)
    {
        if(jid) {
            document.querySelector('#chat_widget').dataset.jid = jid;
        }

        var textarea = document.querySelector('#chat_textarea');

        setTimeout(function() {
            var textarea = document.querySelector('#chat_textarea');
            textarea.value = localStorage.getItem(textarea.dataset.jid + '_message');
        }, 0); // Fix Me

        textarea.onkeydown = function(event) {
            if(event.keyCode == 38 && this.value == '') {
                Chat_ajaxLast(this.dataset.jid);
            } else if(event.keyCode == 40
            && (this.value == '' || Chat.edit == true)) {
                Chat.clearReplace();
            }
        };

        textarea.onkeypress = function(event) {
            if(event.keyCode == 13) {
                if(event.shiftKey) {
                    return;
                }
                Chat.state = 0;
                Chat.sendMessage(this.dataset.jid, Boolean(this.dataset.muc));

                return false;
            } else if(!Boolean(this.dataset.muc)) {
                if(Chat.state == 0 || Chat.state == 2) {
                    Chat.state = 1;
                    Chat_ajaxSendComposing(this.dataset.jid);
                    Chat.since = new Date().getTime();
                }
            }
        };

        textarea.onkeyup = function(event) {
            localStorage.setItem(this.dataset.jid + '_message', this.value);

            setTimeout(function()
            {
                var textarea = document.querySelector('#chat_textarea');

                if(textarea
                && !Boolean(textarea.dataset.muc)
                && Chat.state == 1
                && Chat.since + 5000 < new Date().getTime()) {
                    Chat.state = 2;
                    Chat_ajaxSendPaused(textarea.dataset.jid);
                }
            },5000);

            Chat.toggleAction(this.value.length);
        };

        textarea.oninput = function() {
            MovimUtils.textareaAutoheight(this);
        };

        if(document.documentElement.clientWidth > 1024) {
            document.querySelector('#chat_textarea').focus();
        }
    },
    setTextarea: function(value)
    {
        Chat.edit = true;
        var textarea = document.querySelector('#chat_textarea');
        textarea.value = value;
        MovimUtils.textareaAutoheight(textarea);

    },
    clearReplace: function()
    {
        Chat.edit = false;
        var textarea = document.querySelector('#chat_textarea');
        textarea.value = '';
        MovimUtils.textareaAutoheight(textarea);
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
            if(discussion.dataset.muc != true) {
                if(this.scrollTop < 1) {
                    var chat = document.querySelector('#chat_widget');
                    console.log(chat.dataset);
                    Chat_ajaxGetHistory(chat.dataset.jid, Chat.date);
                }
            }
            Chat.lastHeight = this.clientHeight;
        };
    },
    appendMucMessages : function(date, messages) {
        id = MovimUtils.cleanupId(messages[0].jidfrom + '_conversation');
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

            if (messages[i].sticker != null) {
                MovimUtils.addClass(bubble.querySelector('p.message'), 'sticker');
                bubble.querySelector('p.message').appendChild(Chat.getStickerHtml(messages[i].sticker));
            } else {
                bubble.querySelector('p.message').innerHTML = messages[i].body.replace(/\r\n?|\n/g, '<br />');
            }

            if (messages[i].quoted) {
                bubble.querySelector('p.message').classList.add('quoted');
            }

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
        Chat.date = null;
        if(page) {
            var scrolled = MovimTpl.isPanelScrolled();

            var discussion = document.querySelector('#chat_widget div.contained');

            if(discussion == null) return;

            Chat.lastScroll = discussion.scrollHeight;

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
            if(scrolled && prepend !== true) {
                setTimeout(function() {
                    MovimTpl.scrollPanel();
                }, 20);
            }

            if(prepend) {
                // And we scroll where we were
                var scrollDiff = discussion.scrollHeight - Chat.lastScroll;
                discussion.scrollTop += scrollDiff;
                Chat.lastScroll = discussion.scrollHeight;
            }
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

            id = MovimUtils.cleanupId(id);

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

            if (data[i].rtl) {
                bubble.querySelector('div.bubble').setAttribute('dir', 'rtl');
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

            // We prepend
            if (!mergeMsg)
                MovimTpl.prepend("#" + id, bubble.outerHTML);
        } else {
            if (!mergeMsg) {
                MovimTpl.append("#" + id, bubble.outerHTML);
            }
        }
    },
    getStickerHtml: function(sticker) {
        var img = document.createElement("img");
        if(sticker.url) {
            img.setAttribute("src", sticker.url);
            if(sticker.width)  img.setAttribute("width", sticker.width);
            if(sticker.height)
                img.setAttribute("height", sticker.height);
            else {
                img.setAttribute("height", "150");
            }
        }

        if(sticker.picture) {
            var a = document.createElement("a");
            a.setAttribute("href", sticker.url);
            a.setAttribute("target", "_blank");
            a.appendChild(img);
            return a;
        } else {
            return img;
        }
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
        if(send_button && attachment_button) {
            if(l > 0){
                MovimUtils.showElement(send_button);
                MovimUtils.hideElement(attachment_button);
            } else {
                MovimUtils.showElement(attachment_button);
                MovimUtils.hideElement(send_button);
            }
        }
    }
};

MovimWebsocket.attach(function() {
    var jid = MovimUtils.urlParts().params[0];
    var room = MovimUtils.urlParts().params[1];
    if(jid) {
        MovimTpl.showPanel();

        if(room) {
            Chat_ajaxGetRoom(jid);
        } else {
            Chat_ajaxGet(jid);
            Notification.current('chat|' + jid);
        }
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

window.addEventListener('resize', function() {
    var discussion = document.querySelector('#chat_widget div.contained');
    if(discussion) {
        discussion.scrollTop += Chat.lastHeight - discussion.clientHeight;
        Chat.lastHeight = discussion.clientHeight;
    }
});

var state = 0;
