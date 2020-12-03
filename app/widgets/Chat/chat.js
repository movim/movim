var Chat = {
    left : null,
    right: null,
    date: null,
    separator: null,
    currentDate: null,
    edit: false,

    // Scroll
    lastHeight: null,
    lastScroll: null,

    // Chat state
    composing: false,
    since: null,
    sended: false,

    // Autocomplete vars.
    autocompleteList: null,
    lastAutocomplete: null,
    searchAutocomplete: null,

    // Touch
    startX: 0,
    startY: 0,
    translateX: 0,
    translateY: 0,
    slideAuthorized: false,

    autocomplete: function(event, jid)
    {
        RoomsUtils_ajaxMucUsersAutocomplete(jid);
    },
    onAutocomplete: function(usersList)
    {
        Chat.autocompleteList = usersList;
        usersList = Object.values(usersList);

        var textarea = Chat.getTextarea();

        var words = textarea.value.toLowerCase().trim().split(' ');
        var last = words[words.length - 1].trim();

        if (last == '') {
            // Space or nothing, so we put the first one in the list
            textarea.value += usersList[0] + ' ';
            Chat.lastAutocomplete = usersList[0];
            Chat.searchAutocomplete = null;
        } else if (typeof Chat.lastAutocomplete === 'string'
        && Chat.lastAutocomplete.toLowerCase() == last
        && Chat.searchAutocomplete == null) {
            var index = (usersList.indexOf(Chat.lastAutocomplete) == usersList.length - 1)
                ? -1
                : usersList.indexOf(Chat.lastAutocomplete);

            if (textarea.value.slice(-1) == ' ') textarea.value = textarea.value.trim() + ' ';

            // Full complete, so we iterate
            Chat.lastAutocomplete = usersList[index + 1];
            textarea.value = textarea.value.slice(0, -last.length - 1) + Chat.lastAutocomplete + ' ';
            Chat.searchAutocomplete = null;
        } else {
            // Searching for nicknames starting with
            if (Chat.lastAutocomplete ==  null
            || last != Chat.lastAutocomplete.toLowerCase()) {
                Chat.searchAutocomplete = last;
                Chat.lastAutocomplete = null;
            }

            var start = (typeof Chat.lastAutocomplete === 'string')
                ? usersList.indexOf(Chat.lastAutocomplete) + 1
                : start = 0;

            for (var i = start; i < usersList.length; i++) {
                if (Chat.searchAutocomplete == usersList[i].substring(0, Chat.searchAutocomplete.length).toLowerCase()) {
                    textarea.value = textarea.value.trim().slice(0, -last.length) + usersList[i] + ' ';
                    Chat.lastAutocomplete = usersList[i];
                    break;
                }
            }
        }
    },
    quoteMUC: function(nickname, add)
    {
        var textarea = Chat.getTextarea();
        if (add) {
            if (textarea.value.search(nickname) === -1) {
                textarea.value = nickname + ' ' + textarea.value;
            }
        } else {
            textarea.value = nickname + ' ';
        }

        textarea.focus();
    },
    insertAtCursor: function(textToInsert)
    {
        textarea = Chat.getTextarea();

        const value = textarea.value;
        const start = textarea.selectionStart;
        const end = textarea.selectionEnd;

        textarea.value = value.slice(0, start) + textToInsert + value.slice(end);
        textarea.selectionStart = textarea.selectionEnd = start + textToInsert.length;

        textarea.focus();
        Chat.toggleAction();
    },
    sendMessage: function()
    {
        var textarea = Chat.getTextarea();

        var text = textarea.value;
        var muc = Boolean(textarea.dataset.muc);
        var jid = textarea.dataset.jid;

        Chat.removeSeparator();
        Chat.scrollTotally();

        // In case it was in edit mode
        textarea.classList.remove('edit');

        textarea.focus();

        if (!Chat.sended) {
            Chat.sended = true;

            document.querySelector('.chat_box span.send').classList.add('sending');

            let xhr;

            if (Chat.edit) {
                Chat.edit = false;
                xhr = Chat_ajaxHttpDaemonCorrect(jid, text, textarea.dataset.mid);
                delete textarea.dataset.mid;
            } else {
                var reply = document.querySelector('#reply > div');
                replyMid = false;

                if (reply) {
                    replyMid = reply.dataset.mid;
                    reply.remove();
                };

                xhr = Chat_ajaxHttpDaemonSendMessage(jid, text, muc, false, false, false, replyMid);
            }

            xhr.onreadystatechange = function() {
                if (this.readyState == 4) {
                    if (this.status >= 200 && this.status < 400) {
                        Chat.sendedMessage();
                    }

                    if (this.status >= 400 || this.status == 0) {
                        Chat.failedMessage();
                    }
                }
            };
        }
    },
    sendedMessage: function()
    {
        Chat.sended = false;

        document.querySelector('.chat_box span.send').classList.remove('sending');

        var textarea = Chat.getTextarea();
        localStorage.removeItem(textarea.dataset.jid + '_message');
        Chat.clearReplace();
        Chat.toggleAction();
    },
    failedMessage: function()
    {
        Notification.toast(Chat.delivery_error);
        Chat.sended = false;
        document.querySelector('.chat_box span.send').classList.remove('sending');
    },
    clearReplace: function()
    {
        Chat.edit = false;
        var textarea = Chat.getTextarea();
        textarea.value = localStorage.getItem(textarea.dataset.jid + '_message');
        MovimUtils.textareaAutoheight(textarea);
    },
    editPrevious: function()
    {
        var textarea = Chat.getTextarea();
        if (textarea.value == ''
        && Boolean(textarea.dataset.muc) == false) {
            Chat_ajaxLast(textarea.dataset.jid);
        }
    },
    editMessage: function(mid)
    {
        var textarea = Chat.getTextarea();
        if (textarea.value == ''
        && Boolean(textarea.dataset.muc) == false) {
            Chat_ajaxEdit(mid);
        }
    },
    resolveMessage: function(mid)
    {
        ChatActions_ajaxHttpResolveMessage(mid);
    },
    refreshMessage: function(mid)
    {
        Chat_ajaxRefreshMessage(mid);
    },
    focus: function()
    {
        Chat.sended = false;
        Chat.composing = false;
        Chat.clearReplace();
        Chat.toggleAction();

        var textarea = Chat.getTextarea();
        textarea.onkeydown = function(event) {
            if ((event.keyCode == 37 && Chat.tryPreviousEmoji())
             || (event.keyCode == 39 && Chat.tryNextEmoji())) {
                event.preventDefault();
                return;
            }

            if (this.dataset.muc
            && event.keyCode == 9) {
                event.preventDefault();
                if (Chat.autocompleteList == null) {
                    Chat.autocomplete(event, this.dataset.jid);
                } else {
                    Chat.onAutocomplete(Chat.autocompleteList);
                }
                return;
            }

            if (event.keyCode == 38) {
                Chat.editPrevious();
            } else if (event.keyCode == 40
            && (this.value == '' || Chat.edit == true)) {
                localStorage.removeItem(textarea.dataset.jid + '_message');
                Chat.clearReplace();
            }
        };

        textarea.onkeypress = function(event) {
            if (event.keyCode == 13) {
                // An emoji was selected
                var emoji = document.querySelector('.chat_box .emojis img.selected');
                if (emoji) {
                    Chat.selectEmoji(emoji);
                    event.preventDefault();
                    return;
                }

                if ((MovimUtils.isMobile() && !event.shiftKey)
                || (!MovimUtils.isMobile() && event.shiftKey)) {
                    return;
                }

                Chat.composing = false;
                Chat.sendMessage();

                return false;
            } else if (Chat.composing === false) {
                Chat.composing = true;
                Chat_ajaxSendComposing(this.dataset.jid, Boolean(this.dataset.muc));
                Chat.since = new Date().getTime();
            }
        };

        textarea.onkeyup = function(event) {
            localStorage.setItem(this.dataset.jid + '_message', this.value);

            // A little timeout to not spam the server with composing states
            setTimeout(function()
            {
                if (Chat.since + 3000 < new Date().getTime()) {
                    Chat.composing = false;
                }
            }, 3000);

            Chat.toggleAction();
        };

        textarea.oninput = function() {
            MovimUtils.textareaAutoheight(this);
            Chat.checkEmojis(this.value);
            Chat.scrollRestore();
        }

        textarea.onchange = function() {
            Chat.toggleAction();
        };

        if (document.documentElement.clientWidth > 1024) {
            textarea.focus();
        }

        Chat.autocompleteList = null;
    },
    checkEmojis: function(value, reaction, noColon)
    {
        value = value.toLowerCase();

        listSelector = reaction
            ? '#emojisearchbar + .emojis .results'
            : '.chat_box .emojis';

        var emojisList = document.querySelector(listSelector);
        emojisList.innerHTML = '';

        if (!value) return;

        if (noColon || value.lastIndexOf(':') > -1 && value.length > value.lastIndexOf(':') + 2) {
            var first = true;

            Object.keys(emojis).filter(key => key.indexOf(
                    value.substr(value.lastIndexOf(':') + 1)
                ) > -1)
            .slice(0, 40)
            .forEach(found => {
                var img = document.createElement('img');
                img.setAttribute('src','theme/img/emojis/svg/' + emojis[found].c + '.svg');
                img.classList.add('emoji');
                if (reaction) img.classList.add('large');

                if (first) {
                    img.classList.add('selected');
                    first = false;
                }

                img.title = ':' + found + ':';
                img.dataset.emoji = emojis[found].e;

                if (!reaction) {
                    img.addEventListener('click', e => {
                        Chat.selectEmoji(e.target);
                    });
                }

                emojisList.appendChild(img);
            });
        }
    },
    selectEmoji: function(emoji)
    {
        var emojisList = document.querySelector('.chat_box .emojis');
        var textarea = Chat.getTextarea();

        textarea.value = textarea.value.substr(0, textarea.value.lastIndexOf(':'));
        emojisList.innerHTML = '';
        Chat.insertAtCursor(emoji.dataset.emoji + ' ');
    },
    tryNextEmoji: function()
    {
        var currentEmoji = document.querySelector('.chat_box .emojis img.selected');

        if (currentEmoji && currentEmoji.nextSibling) {
            currentEmoji.classList.remove('selected');
            currentEmoji.nextSibling.classList.add('selected');
            return true;
        }

        return false;
    },
    tryPreviousEmoji: function()
    {
        var currentEmoji = document.querySelector('.chat_box .emojis img.selected');

        if (currentEmoji && currentEmoji.previousSibling) {
            currentEmoji.classList.remove('selected');
            currentEmoji.previousSibling.classList.add('selected');
            return true;
        }

        return false;
    },
    setTextarea: function(value, mid)
    {
        Chat.edit = true;
        var textarea = Chat.getTextarea();
        textarea.value = value;
        textarea.classList.add('edit');

        if (mid) {
            textarea.dataset.mid = mid;
        }

        MovimUtils.textareaAutoheight(textarea);
        textarea.focus();
    },
    setGeneralElements(date, separator)
    {
        var div = document.createElement('div');

        Chat.currentDate = null;

        div.innerHTML = date;
        Chat.date = div.firstChild.cloneNode(true);
        div.innerHTML = separator;
        Chat.separator = div.firstChild.cloneNode(true);
    },
    setSpecificElements : function(left, right)
    {
        var div = document.createElement('div');

        Chat.currentDate = null;

        div.innerHTML = left;
        Chat.left = div.firstChild.cloneNode(true);
        div.innerHTML = right;
        Chat.right = div.firstChild.cloneNode(true);
    },
    setScrollBehaviour : function()
    {
        var discussion = Chat.getDiscussion();
        if (discussion == null) return;

        discussion.onscroll = function() {
            if (this.scrollTop < 1
            && discussion.querySelectorAll('ul li div.bubble p').length >= Chat.pagination) {
                Chat_ajaxGetHistory(
                    Chat.getTextarea().dataset.jid,
                    Chat.currentDate,
                    Chat.getTextarea().dataset.muc,
                    true);
            }

            Chat.setScroll();
        };

        Chat.setScroll();
    },
    setScroll : function ()
    {
        var discussion = Chat.getDiscussion();
        if (discussion == null) return;

        Chat.lastHeight = discussion.scrollHeight;
        Chat.lastScroll = discussion.scrollTop + discussion.clientHeight;

        Chat.scrollToggleButton();
    },
    isScrolled : function ()
    {
        return Chat.lastHeight -5 <= Chat.lastScroll;
    },
    scrollRestore : function ()
    {
        var discussion = Chat.getDiscussion();
        if (!discussion) return;

        if (Chat.isScrolled()) {
            Chat.scrollTotally();
        } else {
            discussion.scrollTop = Chat.lastScroll - discussion.clientHeight;
        }
    },
    scrollTotally : function ()
    {
        var discussion = Chat.getDiscussion();
        if (discussion == null) return;

        discussion.scrollTop = discussion.scrollHeight;
    },
    scrollToSeparator : function ()
    {
        var discussion = Chat.getDiscussion();
        if (discussion == null) return;

        var separator = discussion.querySelector('.separator');
        if (separator) {
            discussion.scrollTop = separator.offsetTop - 65;
            Chat.setScroll();
        }
    },
    scrollToggleButton : function ()
    {
        var discussion = Chat.getDiscussion();
        if (discussion == null) return;

        var button = discussion.querySelector('.button.action');

        if (Chat.isScrolled()) {
            button.classList.remove('show');
        } else {
            button.classList.add('show');
        }
    },
    removeSeparator: function()
    {
        var separator = Chat.getDiscussion().querySelector('li.separator');
        if (separator) separator.remove();
    },
    setReactionButtonBehaviour : function()
    {
        let reactions = document.querySelectorAll('#chat_widget span.reaction');
        let i = 0;

        while (i < reactions.length) {
            reactions[i].onclick = function() {
                Stickers_ajaxReaction(this.dataset.mid);
            }

            i++;
        }
    },
    setParentScrollBehaviour : function()
    {
        let toParents = document.querySelectorAll('#chat_widget div.parent');
        let i = 0;

        while (i < toParents.length) {
            toParents[i].onclick = function() {
                var parentMsg = document.getElementById(this.dataset.parentId);
                if (!parentMsg) {
                    parentMsg = document.getElementById(this.dataset.parentReplaceId)
                }
                if (parentMsg) {
                    scrollToLi = parentMsg.parentNode.parentNode;
                    document.querySelector('#chat_widget .contained').scrollTo({
                        top: scrollToLi.offsetTop - 60,
                        left: 0,
                        behavior: 'smooth'
                    });
                }
            }

            i++;
        }
    },
    setReplyButtonBehaviour : function()
    {
        let replies = document.querySelectorAll('#chat_widget span.reply');
        let i = 0;

        while (i < replies.length) {
            replies[i].onclick = function() {
                Chat_ajaxHttpDaemonReply(this.dataset.mid);
            }

            i++;
        }
    },
    setActionsButtonBehaviour : function()
    {
        let actions = document.querySelectorAll('#chat_widget .contained:not(.muc) span.actions');
        let i = 0;

        while (i < actions.length) {
            actions[i].onclick = function() {
                ChatActions_ajaxShowMessageDialog(this.dataset.mid);
            }

            i++;
        }
    },
    checkDiscussion : function(page)
    {
        for (var firstKey in page) break;
        if (page[firstKey] == null) return false;

        for (var firstMessageKey in page[firstKey]) break;
        var firstMessage = page[firstKey][firstMessageKey];
        if (firstMessage == null) return false;

        var contactJid = firstMessage.user_id == firstMessage.jidfrom
            ? firstMessage.jidto
            : firstMessage.jidfrom;

        if (document.getElementById(MovimUtils.cleanupId(contactJid) + '-discussion')
        == null) return false;

        return true;
    },
    appendMessagesWrapper : function(page, prepend)
    {
        var discussion = Chat.getDiscussion();

        if (page && Chat.checkDiscussion(page)) {
            if (discussion == null) return;

            Chat.setScroll();

            for(date in page) {
                if (prepend === undefined || prepend === false) {
                    Chat.appendDate(date, prepend);
                }

                for(speakertime in page[date]) {
                    if (!Chat.currentDate) {
                        Chat.currentDate = page[date][speakertime].published;
                    }

                    Chat.appendMessage(speakertime, page[date][speakertime], prepend);
                }

                if (prepend && date) {
                    Chat.appendDate(date, prepend);
                }
            }

            if (prepend) {
                // And we scroll where we were
                var scrollDiff = discussion.scrollHeight - Chat.lastHeight;
                discussion.scrollTop += scrollDiff;

                Chat.setScroll();
            } else {
                Chat.scrollRestore();
            }

            var chat = document.querySelector('#chat_widget');
            var lastMessage = chat.querySelector('ul li:not(.oppose):last-child div.bubble > div:last-child');
            var textarea = Chat.getTextarea();

            if (textarea && lastMessage) {
                Chat_ajaxDisplayed(
                    textarea.dataset.jid,
                    lastMessage.id
                );
            }
        } else if (discussion !== null) {
            if (discussion.querySelector('ul').innerHTML === '') {
                discussion.querySelector('ul').classList.remove('spin');
                discussion.querySelector('.placeholder').classList.add('show');
            }
        }

        Chat.setScrollBehaviour();
        Chat.setReactionButtonBehaviour();
        Chat.setReplyButtonBehaviour();
        Chat.setActionsButtonBehaviour();
        Chat.setParentScrollBehaviour();
    },
    appendMessage : function(idjidtime, data, prepend) {
        if (data.body === null) return;

        var bubble = null,
            mergeMsg = false,
            msgStack,
            refBubble;

        var isMuc = (document.querySelector('#chat_widget div.contained').dataset.muc == 1);
        var jidtime = idjidtime.substring(idjidtime.indexOf('<') + 1);

        if (prepend) {
            refBubble = document.querySelector('#chat_widget .contained section > ul > li:first-child');
            msgStack = document.querySelector("[data-bubble='" + jidtime + "']");
        } else {
            refBubble = document.querySelector("#chat_widget .contained section > ul > li:last-child");
            var stack = document.querySelectorAll("[data-bubble='" + jidtime + "']");
            msgStack = stack[stack.length-1];
        }

        if (msgStack != null
            && msgStack.parentNode == refBubble
            && data.url === false
            && (data.file === undefined || data.file === null)
            && (data.sticker === undefined || data.sticker === null)
            && !refBubble.querySelector('div.bubble').classList.contains('sticker')
            && !refBubble.querySelector('div.bubble').classList.contains('file')
            && ['jingle_start'].indexOf(data.type) < 0
        ) {
            bubble = msgStack.parentNode;
            mergeMsg = true;
        } else {
            if (data.user_id == data.jidfrom
            || data.mine) {
                bubble = Chat.right.cloneNode(true);
                if (data.mine) {
                    id = data.jidfrom;
                } else {
                    id = data.jidto;
                }
            } else {
                bubble = Chat.left.cloneNode(true);
                id = data.jidfrom;
            }

            id = MovimUtils.cleanupId(id) + '-conversation';

            bubble.querySelector('div.bubble').dataset.bubble = jidtime;
            bubble.querySelector('div.bubble').dataset.publishedprepared = data.publishedPrepared;
        }

        if (isMuc) {
            bubble.dataset.resource = data.resource;
        }

        if (refBubble.dataset.resource == bubble.dataset.resource
            && mergeMsg == false
            && isMuc) {
            if (prepend) {
                refBubble.classList.add('sequel');
            } else {
                bubble.classList.add('sequel');
            }
        }

        if (['jingle_start'].indexOf(data.type) >= 0) {
            bubble.querySelector('div.bubble').classList.add('call');
        }

        var msg = bubble.querySelector('div.bubble > div');
        var span = msg.querySelector('span:not(.reaction)');
        var p = msg.getElementsByTagName('p')[0];
        var reaction = msg.querySelector('span.reaction');
        var reply = msg.querySelector('span.reply');
        var actions = msg.querySelector('span.actions');
        var reactions = msg.querySelector('ul.reactions');

        // If there is already a msg in this bubble, create another div (next msg or replacement)
        if (bubble.querySelector('div.bubble p')
        && bubble.querySelector('div.bubble p').innerHTML != '') {
            msg = document.createElement('div');
            span = document.createElement('span');
            span.className = 'info';
            p = document.createElement('p');
            reaction = reaction.cloneNode(true);

            if (reply) {
                reply = reply.cloneNode(true);
            }

            if (actions) {
                actions = actions.cloneNode(true);
            }

            reactions = document.createElement('ul');
            reactions.className = 'reactions';
        }

        if (data.retracted) {
            p.classList.add('retracted');
        }

        if (data.encrypted) {
            p.classList.add('encrypted');
        }

        if (data.body.match(/^\/me\s/)) {
            p.classList.add('quote');
            data.body = data.body.substr(4);
        }

        if (data.body.match(/^\/code\s/)) {
            p.classList.add('code');
            data.body = data.body.substr(6).trim();
        }

        if (data.id != null) {
            msg.setAttribute('id', data.id);
        }

        if (data.rtl) {
            msg.setAttribute('dir', 'rtl');
        }

        if (data.sticker != null) {
            bubble.querySelector('div.bubble').classList.add('sticker');
            p.appendChild(Chat.getStickerHtml(data.sticker));

            if (data.file != null) {
                p.classList.add('previewable');
            }
        } else {
            p.innerHTML = data.body;
        }

        if (data.file != null && data.card === undefined && data.file.type !== 'xmpp') {
            bubble.querySelector('div.bubble').classList.add('file');

            // Ugly fix to clear the paragraph if the file contains a similar link
            if (p.querySelector('a') && p.querySelector('a').href == data.file.uri) {
                p.innerHTML = '';
            }

            p.appendChild(Chat.getFileHtml(data.file, data.sticker));
        }

        if (data.oldid) {
            span.appendChild(Chat.getEditedIcoHtml());
        }

        if (data.user_id == data.jidfrom) {
            if (data.displayed) {
                span.appendChild(Chat.getDisplayedIcoHtml(data.displayed));
            } else if (data.delivered) {
                span.appendChild(Chat.getDeliveredIcoHtml(data.delivered));
            }
        }

        if (data.reactionsHtml !== undefined) {
            reactions.innerHTML = data.reactionsHtml;
        }

        if (data.card) {
            bubble.querySelector('div.bubble').classList.add('file');
            msg.appendChild(Chat.getCardHtml(data.card));
        }

        if (isMuc) {
            var resourceSpan = document.createElement('span');
            resourceSpan.classList.add('resource');
            resourceSpan.classList.add(data.color);
            resourceSpan.innerText = data.resource;

            msg.appendChild(resourceSpan);
        }

        // Parent
        if (data.parent) {
            msg.appendChild(Chat.getParentHtml(data.parent));
        }

        msg.appendChild(p);
        msg.appendChild(reactions);
        msg.appendChild(span);

        if (data.thread !== null) {
            reply.dataset.mid = data.mid;
            msg.appendChild(reply);
        }

        reaction.dataset.mid = data.mid;
        msg.appendChild(reaction);

        if (actions) {
            actions.dataset.mid = data.mid;
            msg.appendChild(actions);
        }

        var elem = document.getElementById(data.oldid);
        if (!elem) {
            elem = document.getElementById(data.id);
        }

        if (elem) {
            elem.parentElement.replaceChild(msg, elem);
            mergeMsg = true;

            // If the previous message was not a file and is replaced by it
            if (data.file != null) {
                msg.parentElement.classList.add('file');
            }

            if (data.sticker != null) {
                msg.parentElement.classList.add('sticker');
            }
        } else {
            if (prepend) {
                bubble.querySelector('div.bubble').insertBefore(msg, bubble.querySelector('div.bubble').firstChild);
            } else {
                bubble.querySelector('div.bubble').appendChild(msg);
            }
        }

        /* MUC specific */
        if (isMuc) {
            if (data.moderator) {
                bubble.querySelector('div.bubble').classList.add('moderator');
            }

            icon = bubble.querySelector('span.primary.icon');

            if (icon.querySelector('img') == undefined) {
                if (data.icon_url) {
                    var img = document.createElement('img');
                    img.setAttribute('src', data.icon_url);

                    icon.appendChild(img);
                } else {
                    icon.classList.add('color');
                    icon.classList.add(data.color);
                    icon.innerHTML = data.icon;
                }

                icon.dataset.resource = data.resource;
            }

            if (data.quoted) {
                bubble.querySelector('div.bubble').classList.add('quoted');
            }
        }

        if (prepend) {
            Chat.currentDate = data.published;

            // We prepend
            if (!mergeMsg) {
                MovimTpl.prepend('#' + id, bubble.outerHTML);
            }
        } else {
            if (!mergeMsg) {
                MovimTpl.append('#' + id, bubble.outerHTML);
            }
        }
    },
    appendDate: function(date, prepend) {
        var list = document.querySelector('#chat_widget > div ul');

        if (document.getElementById(MovimUtils.cleanupId(date)) && !prepend) return;

        dateNode = Chat.date.cloneNode(true);
        dateNode.dataset.value = date;
        dateNode.querySelector('p').innerHTML = date;
        dateNode.id = MovimUtils.cleanupId(date);

        var dates = list.querySelectorAll('li.date');

        if (prepend) {
            // If the date was already displayed we remove it
            if (dates.length > 0
            && dates[0].dataset.value == date) {
                dates[0].parentNode.removeChild(dates[0]);
            }

            list.insertBefore(dateNode, list.firstChild);
        } else {
            if (dates.length > 0
            && dates[dates.length-1].dataset.value == date) {
                return;
            }

            list.appendChild(dateNode);
        }
    },
    insertSeparator: function(counter) {
        separatorNode = Chat.separator.cloneNode(true);

        var list = document.querySelector('#chat_widget > div ul');

        if (!list || list.querySelector('li.separator')) return;

        var messages = document.querySelectorAll('#chat_widget > div ul div.bubble p');

        if (messages.length > counter && counter > 0) {
            var p = messages[messages.length - counter];
            list.insertBefore(separatorNode, p.parentNode.parentNode.parentNode);
        }
    },
    getStickerHtml: function(sticker) {
        var img = document.createElement('img');
        if (sticker.url) {
            if (sticker.thumb) {
                img.setAttribute('src', sticker.thumb);
            } else {
                img.setAttribute('src', sticker.url);
            }

            if (sticker.width)  img.setAttribute('width', sticker.width);
            if (sticker.height) {
                img.setAttribute('height', sticker.height);
            } else {
                img.setAttribute('height', '170');
            }
        }

        if (sticker.title) {
            img.title = sticker.title;
        }

        if (sticker.picture) {
            img.classList.add('active');
            img.setAttribute('onclick', 'Preview_ajaxShow("' + sticker.url + '")');
        }

        return img;
    },
    getCardHtml: function(card) {
        var ul = document.createElement('ul');
        ul.setAttribute('class', 'card list noanim active');

        ul.innerHTML = card;
        return ul;
    },
    getFileHtml: function(file, sticker) {
        var div = document.createElement('div');
        div.setAttribute('class', 'file');

        if (file.name) {
            if (file.type == 'video/webm' || file.type == 'video/mp4') {
                var video = document.createElement('video');
                video.setAttribute('src', file.uri);
                video.setAttribute('controls', 'controls');
                video.setAttribute('loop', 'loop');

                // Tenor implementation
                if (file.host && file.host == 'media.tenor.com') {
                    video.setAttribute('autoplay', 'autoplay');
                }

                div.appendChild(video);
            }

            // Tenor implementation
            if (file.host && file.host == 'media.tenor.com') {
                return div;
            }

            var a = document.createElement('a');

            if (sticker == null) {
                var link = document.createElement('p');
                link.textContent = file.name;
                link.setAttribute('title', file.name);
                a.appendChild(link);
            }
            a.setAttribute('href', file.uri);
            a.setAttribute('target', '_blank');
            a.setAttribute('rel', 'noopener');

            div.appendChild(a);

            if (file.host) {
                var host = document.createElement('span');
                host.innerHTML = file.host;
                host.setAttribute('class', 'host');

                a.appendChild(host);
            }

            var span = document.createElement('span');
            span.innerHTML = file.cleansize;
            span.setAttribute('class', 'size');

            a.appendChild(span);
        }

        return div;
    },
    getEditedIcoHtml: function() {
        var i = document.createElement('i');
        i.className = 'material-icons';
        i.innerText = 'edit';
        return i;
    },
    getDeliveredIcoHtml: function(delivered) {
        var i = document.createElement('i');
        i.className = 'material-icons';
        i.innerText = 'check';
        i.setAttribute('title', delivered);
        return i;
    },
    getParentHtml: function(parent) {
        var div = document.createElement('div');
        div.classList.add('parent');
        div.dataset.parentReplaceId = parent.replaceid
        div.dataset.parentId = parent.id;

        var span = document.createElement('span');

        if (parent.color) {
            span.classList.add('resource');
            span.classList.add(parent.color);
        }

        span.classList.add('from');
        span.innerHTML = parent.fromName;
        div.appendChild(span);

        var p = document.createElement('p');
        p.innerHTML = parent.body;
        div.appendChild(p);

        return div;
    },
    getDisplayedIcoHtml: function(displayed) {
        var i = document.createElement('i');
        i.className = 'material-icons';
        i.innerText = 'done_all';
        i.setAttribute('title', displayed);
        return i;
    },
    toggleAction: function() {
        var chatBox = document.querySelector('#chat_widget .chat_box');

        if (chatBox) {
            if (Chat.getTextarea().value.length > 0) {
                chatBox.classList.add('compose');
                Chat.toggleAttach(true);
            } else {
                chatBox.classList.remove('compose');
            }
        }
    },
    toggleAttach: function(forceDisabled)
    {
        var attach = document.querySelector('#chat_widget .chat_box span.attach');

        if (attach) {
            if (forceDisabled) {
                attach.classList.remove('enabled');
            } else {
                attach.classList.toggle('enabled');
            }
        }
    },
    getTextarea: function() {
        var textarea = document.querySelector('#chat_textarea');
        if (textarea) return textarea;
    },
    getDiscussion: function() {
        return document.querySelector('#chat_widget div.contained');
    },
    touchEvents: function() {
        var chat = document.querySelector('#chat_widget');
        clientWidth = Math.abs(document.body.clientWidth);

        chat.addEventListener('touchstart', function(event) {
            Chat.startX = event.targetTouches[0].pageX;
            Chat.startY = event.targetTouches[0].pageY;
            chat.classList.remove('moving');
        }, true);

        chat.addEventListener('touchmove', function(event) {
            moveX = event.targetTouches[0].pageX;
            moveY = event.targetTouches[0].pageY;
            delay = 20;
            Chat.translateX = parseInt(moveX - Chat.startX);
            Chat.translateY = parseInt(moveY - Chat.startY);

            if (Chat.translateX > delay && Chat.translateX <= clientWidth) {
                // If the horizontal movement is allowed and the vertical one is not important
                // we authorize the slide
                if (Math.abs(Chat.translateY) < delay) {
                    Chat.slideAuthorized = true;
                }

                if (Chat.slideAuthorized) {
                    chat.style.transform = 'matrix3d(1, 0, 0, 0, 0, 1, 0, 0, 0, 0, 1, 0, '
                        + (Chat.translateX - delay)
                        + ', 0, 0, 1)';
                }
            }
        }, true);

        chat.addEventListener('touchend', function(event) {
            chat.classList.add('moving');
            if (Chat.translateX > (clientWidth / 4) && Chat.slideAuthorized) {
                MovimTpl.hidePanel();
                Chat_ajaxGet(null, true);
            }
            chat.style.transform = '';
            Chat.slideAuthorized = false;
            Chat.startX = Chat.translateX = Chat.startY = Chat.translateY = 0;
        }, true);
    }
};

MovimWebsocket.attach(function() {
    Chat_ajaxInit();

    var jid = MovimUtils.urlParts().params[0];
    var room = (MovimUtils.urlParts().params[1] === 'room');
    if (jid) {
        if (Boolean(document.getElementById(MovimUtils.cleanupId(jid) + '-conversation'))) {
            Chat_ajaxGetHistory(jid, Chat.currentDate, room, false);
        } else {
            if (room) {
                Chat_ajaxGetRoom(jid);
            } else {
                Chat_ajaxGet(jid);
            }
        }
    } else {
        Notification.current('chat');
    }
});

if (typeof Upload != 'undefined') {
    Upload.attach(function(file) {
        Chat_ajaxHttpDaemonSendMessage(Chat.getTextarea().dataset.jid, false, Boolean(Chat.getTextarea().dataset.muc), false, false, file);
    });
}

movimAddFocus(function() {
    if (MovimWebsocket.connection) {
        var jid = MovimUtils.urlParts().params[0];
        if (jid) {
            Chat_ajaxGetHeader(jid, (MovimUtils.urlParts().params[1] === 'room'));
        }
    }
});

document.addEventListener('focus', function() {
    var textarea = Chat.getTextarea();
    if (textarea) textarea.focus();
});

window.addEventListener('resize', function() {
    Chat.scrollRestore();
});

movimAddOnload(function() {
    Chat.touchEvents();
});

var state = 0;
