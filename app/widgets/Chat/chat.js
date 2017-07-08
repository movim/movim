var Chat = {
    left : null,
    right: null,
    room: null,
    date: null,
    currentDate: null,
    lastScroll: null,
    lastHeight: null,
    edit: false,

    // Chat state
    state: null,
    since: null,
    sended: false,

    sendMessage: function(jid, muc)
    {
        var n = document.querySelector('#chat_textarea');
        var text = n.value;
        n.focus();

        if(!Chat.sended) {
            Chat.sended = true;

            if(Chat.edit) {
                Chat.edit = false;
                Chat_ajaxCorrect(jid, encodeURIComponent(text));
            } else {
                Chat_ajaxSendMessage(jid, encodeURIComponent(text), muc);
            }
        }
    },

    sendedMessage: function()
    {
        Chat.sended = false;

        var n = document.querySelector('#chat_textarea');
        n.value = "";
        n.focus();
        MovimUtils.textareaAutoheight(n);

        localStorage.removeItem(n.dataset.jid + '_message');
    },

    focus: function(jid)
    {
        Chat.sended = false;

        if(jid) {
            document.querySelector('#chat_widget').dataset.jid = jid;
        } else {
            delete document.querySelector('#chat_widget').dataset.jid;
        }

        var textarea = document.querySelector('#chat_textarea');

        setTimeout(function() {
            var textarea = document.querySelector('#chat_textarea');
            textarea.value = localStorage.getItem(textarea.dataset.jid + '_message');

            MovimUtils.textareaAutoheight(textarea);
            //Chat.adaptDiscussion();
        }, 0); // Fix Me

        textarea.onkeydown = function(event) {
            if(this.dataset.muc) return;

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

            //Chat.adaptDiscussion();
            Chat.toggleAction(this.value.length);
        };

        textarea.oninput = function() {
            MovimUtils.textareaAutoheight(this);
            //Chat.adaptDiscussion();
        };

        if(document.documentElement.clientWidth > 1024) {
            document.querySelector('#chat_textarea').focus();
        }
    },
    adaptDiscussion: function()
    {
        document.querySelector('main > section > div > div.contained').style.height =
            'calc(100% - '+
            document.querySelector('.chat_box').clientHeight +
            'px)';
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
    setBubbles : function(left, right, room, date) {
        var div = document.createElement('div');

        Chat.currentDate = null;

        div.innerHTML = left;
        Chat.left = div.firstChild.cloneNode(true);
        div.innerHTML = right;
        Chat.right = div.firstChild.cloneNode(true);
        div.innerHTML = room;
        Chat.room = div.firstChild.cloneNode(true);
        div.innerHTML = date;
        Chat.date = div.firstChild.cloneNode(true);

        Chat.setScrollBehaviour();
    },
    setScrollBehaviour : function() {
        var discussion = document.querySelector('#chat_widget div.contained');
        discussion.onscroll = function() {
            if(discussion.dataset.muc != true) {
                if(this.scrollTop < 1) {
                    var chat = document.querySelector('#chat_widget');
                    Chat_ajaxGetHistory(chat.dataset.jid, Chat.currentDate);
                }
            }
            Chat.lastHeight = this.clientHeight;
        };
    },
    appendMessagesWrapper : function(page, prepend) {
        if(page) {
            var scrolled = MovimTpl.isPanelScrolled();

            var discussion = document.querySelector('#chat_widget div.contained');

            if(discussion == null) return;

            Chat.lastScroll = discussion.scrollHeight;

            for(date in page) {
                if(prepend === undefined) {
                    Chat.appendDate(date, prepend);
                }

                if (page[date].constructor == Array) {
                    for(id in page[date]) {
                        Chat.appendMucMessage(page[date][id]);
                    }
                } else {
                    for(speakertime in page[date]) {
                        if(!Chat.currentDate) {
                            Chat.currentDate = page[date][speakertime].published;
                        }

                        if(discussion.dataset.muc != 1) {
                            Chat.appendMessage(speakertime, page[date][speakertime], prepend);
                        }
                    }
                }

                if(prepend && date) {
                    Chat.appendDate(date, prepend);
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

            var chat = document.querySelector('#chat_widget');
            var lastMessage = chat.querySelector('ul li:not(.oppose):last-child div.bubble > div:last-child');

            if(chat.dataset.jid && lastMessage) {
                Chat_ajaxDisplayed(
                    chat.dataset.jid,
                    lastMessage.id
                );
            }
        }
    },
    appendMucMessage : function(message) {
        var conversation = document.getElementById(
            MovimUtils.cleanupId(message.jidfrom + '_conversation')
        );

        bubble = Chat.room.cloneNode(true);

        var p = bubble.querySelector('p.message');

        if(message.body.match(/^\/me/)) {
            p.classList.add('quote');
            message.body = message.body.substr(4);
        }

        if(message.body.match(/^\/code/)) {
            p.classList.add('code');
            message.body = message.body.substr(6).trim();
        }

        if (message.quoted) {
            p.classList.add('quoted');
        }

        /*if (message.sticker != null) {
            MovimUtils.addClass(bubble.querySelector('p.message'), 'sticker');
            bubble.querySelector('p.message').appendChild(Chat.getStickerHtml(message.sticker));
        } else {*/
        p.innerHTML = message.body.replace(/\r\n?|\n/g, '<br />');
        //}

        bubble.querySelector('span.info').innerHTML = message.publishedPrepared;

        var user = bubble.querySelector('p.user');
        user.className = 'user ' + message.color;
        user.innerHTML = message.resource;
        user.onclick = function(n) {
            var textarea = document.querySelector('#chat_textarea');
            textarea.value = this.innerHTML + ', ' + textarea.value;
            textarea.focus();
        };

        if(conversation) {
            conversation.appendChild(bubble);
        }
    },
    appendMessage : function(idjidtime, data, prepend) {
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
            && data.file === null
            && data.sticker === null
            && !MovimUtils.hasClass(refBubble.querySelector('div.bubble'), "sticker")
            && !MovimUtils.hasClass(refBubble.querySelector('div.bubble'), "file")
        ){
            bubble = msgStack.parentNode;
            mergeMsg = true;
        } else {
            if (data.session == data.jidfrom) {
                bubble = Chat.right.cloneNode(true);
                id = data.jidto + '_conversation';
            } else {
                bubble = Chat.left.cloneNode(true);
                id = data.jidfrom + '_conversation';
            }

            id = MovimUtils.cleanupId(id);

            bubble.querySelector('div.bubble').dataset.bubble = jidtime;
            bubble.querySelector('div.bubble').dataset.publishedprepared = data.publishedPrepared;
        }

        var msg = bubble.querySelector('div.bubble > div');
        var span = msg.getElementsByTagName('span')[0];
        var p = msg.getElementsByTagName('p')[0];

        // If there is already a msg in this bubble, create another div (next msg or replacement)
        if (bubble.querySelector('div.bubble p')
        && bubble.querySelector('div.bubble p').innerHTML != '') {
            msg = document.createElement("div");
            p = document.createElement("p");
            span = document.createElement("span");
            span.className = 'info';
        }

        if (data.rtl) {
            bubble.querySelector('div.bubble').setAttribute('dir', 'rtl');
        }

        if (data.body.match(/^\/me\s/)) {
            p.className = 'quote';
            data.body = data.body.substr(4);
        }

        if(data.body.match(/^\/code/)) {
            p.classList.add('code');
            data.body = data.body.substr(6).trim();
        }

        if (data.id != null) {
            msg.setAttribute("id", data.id);
            if (data.newid != null) {
                msg.setAttribute("id", data.newid);
            }
        }

        if (data.sticker != null) {
            MovimUtils.addClass(bubble.querySelector('div.bubble'), 'sticker');
            p.appendChild(Chat.getStickerHtml(data.sticker));
        } else {
            p.innerHTML = data.body.replace(/\r\n?|\n/g, '<br />');
        }

        if (data.audio != null) {
            MovimUtils.addClass(bubble.querySelector('div.bubble'), 'file');
            p.appendChild(Chat.getAudioHtml(data.file));
        } else if (data.file != null) {
            MovimUtils.addClass(bubble.querySelector('div.bubble'), 'file');
            p.appendChild(Chat.getFileHtml(data.file, data.sticker));
        }

        if (data.edited) {
            span.appendChild(Chat.getEditedIcoHtml());
        }

        if (data.session == data.jidfrom) {
            if (data.displayed) {
                span.appendChild(Chat.getDisplayedIcoHtml(data.displayed));
            } else if (data.delivered) {
                span.appendChild(Chat.getDeliveredIcoHtml(data.delivered));
            }
        }

        msg.appendChild(p);
        msg.appendChild(span);

        var elem = document.getElementById(data.id);
        if (elem) {
            elem.parentElement.replaceChild(msg, elem);
            mergeMsg = true;
        } else {
            if(prepend) {
                bubble.querySelector('div.bubble').insertBefore( msg, bubble.querySelector('div.bubble').firstChild );
            } else {
                bubble.querySelector('div.bubble').appendChild(msg);
            }
        }

        if(prepend){
            Chat.currentDate = data.published;

            // We prepend
            if (!mergeMsg) {
                MovimTpl.prepend("#" + id, bubble.outerHTML);
            }
        } else {
            if (!mergeMsg) {
                MovimTpl.append("#" + id, bubble.outerHTML);
            }
        }
    },
    appendDate: function(date, prepend) {
        var list = document.querySelector('#chat_widget > div ul');
        dateNode = Chat.date.cloneNode(true);
        dateNode.dataset.value = date;
        dateNode.querySelector('p').innerHTML = date;

        var dates = list.querySelectorAll('li.date');

        if(prepend) {
            // If the date was already displayed we remove it
            if(dates.length > 0
            && dates[0].dataset.value == date) {
                dates[0].parentNode.removeChild(dates[0]);
            }

            list.insertBefore(dateNode, list.firstChild);
        } else {
            if(dates.length > 0
            && dates[dates.length-1].dataset.value == date) {
                return;
            }

            list.appendChild(dateNode);
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
    getAudioHtml: function(file) {
        var audio = document.createElement("audio");
        audio.setAttribute("controls", true);

        var source = document.createElement("source");
        source.setAttribute("src", file.uri);
        source.setAttribute("type", file.type);

        audio.appendChild(source);

        return audio;
    },
    getFileHtml: function(file, sticker) {
        var div = document.createElement("div");
        div.setAttribute("class", "file");

        var a = document.createElement("a");
        if(sticker == null) {
            a.innerHTML = file.name;
        }
        a.setAttribute("href", file.uri);
        a.setAttribute("target", "_blank");

        div.appendChild(a);

        var span = document.createElement("span");
        span.innerHTML = file.size;
        span.setAttribute("class", "size");

        a.appendChild(span);

        return div;
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
    getDisplayedIcoHtml: function(displayed) {
        var i = document.createElement("i");
        i.setAttribute("class", "zmdi zmdi-check-all");
        i.setAttribute("title", displayed);
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
    Upload.attach(function(file) {
        var textarea = document.querySelector('#chat_textarea');

        Chat_ajaxSendMessage(textarea.dataset.jid, false, Boolean(textarea.dataset.muc), false, false, file);
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

