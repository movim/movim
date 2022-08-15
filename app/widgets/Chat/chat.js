var Chat = {
    left : null,
    right: null,
    date: null,
    separator: null,

    // Date time of the oldest message displayed
    currentDateTime: null,
    edit: false,

    // Scroll
    lastHeight: null,
    lastScroll: null,

    // Intersection discussion observer
    discussionObserver: null,

    // Chat state
    composing: false,
    since: null,
    sent: false,

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

    // Temporary messages, for OMEMO local messages
    tempMessages: {},
    // Local list of the room members, used to encrypt the messages
    groupChatMembers: [],

    // Jingle types
    jingleTypes: ['jingle_incoming', 'jingle_outgoing', 'jingle_end'],

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
    get: function(jid, light)
    {
        if (jid != undefined) {
            MovimTpl.showPanel();
            var chat = document.querySelector('#chat_widget');
            chat.innerHTML = '';
        }

        Chat_ajaxGet(jid, light);
    },
    getRoom: function(jid)
    {
        MovimTpl.showPanel();
        var chat = document.querySelector('#chat_widget');
        chat.innerHTML = '';

        Chat_ajaxGetRoom(jid);
    },
    sendMessage: function()
    {
        var textarea = Chat.getTextarea();
        var text = textarea.value;

        var muc = Boolean(textarea.dataset.muc);
        var mucReceipts = false;
        var jid = textarea.dataset.jid;

        if (muc) {
            var counter = document.querySelector('#chat_widget header span.counter');
            mucReceipts = (counter && Boolean(counter.dataset.mucreceipts));
        }

        Chat.removeSeparator();
        Chat.scrollTotally();

        // In case it was in edit mode
        textarea.classList.remove('edit');

        textarea.focus();

        if (!Chat.sent) {
            Chat.enableSending();

            let xhr;
            let timeout = 10000;
            let onTimeout = function() {
                Chat.failedMessage();
            };

            let onReadyStateChange = function() {
                if (this.readyState == 4) {
                    if (this.status >= 200 && this.status < 400) {
                        Chat.sentMessage();
                    }

                    if (this.status >= 400 || this.status == 0) {
                        Chat.failedMessage();
                    }
                }
            };

            if (Chat.edit) {
                Chat.edit = false;

                if (text == '') {
                    Chat.disableSending();
                } else {
                    xhr = Chat_ajaxHttpDaemonCorrect(jid, textarea.dataset.mid, text);
                }

                xhr.timeout = timeout;
                xhr.ontimeout = onTimeout;
                xhr.onreadystatechange = onReadyStateChange;

                delete textarea.dataset.mid;
            } else {
                var reply = document.querySelector('#reply > div');
                replyMid = false;

                if (reply) {
                    replyMid = reply.dataset.mid;
                    reply.remove();
                };

                if (textarea.dataset.encryptedstate == 'build') {
                    var store = new ChatOmemoStorage();
                    store.getLocalRegistrationId().then(deviceId => {
                        if (deviceId) {
                            if (Boolean(textarea.dataset.muc) == true) {
                                var bundlesIds = {};
                                Chat.groupChatMembers.forEach(member => {
                                    let bundles = store.getSessionsIds(member);
                                    if (bundles.length > 0) {
                                        bundlesIds[member] = bundles;
                                    }
                                });

                                ChatOmemo_ajaxGetMissingRoomSessions(jid, bundlesIds);
                            } else {
                                ChatOmemo_ajaxGetMissingSessions(jid, store.getSessionsIds(jid));
                            }
                        } else {
                            Chat.disableSending();
                            ChatOmemo.generateBundle();
                        }
                    });
                } else if (textarea.dataset.encryptedstate == 'yes') {
                    // Try to encrypt the message
                    let omemo = ChatOmemo.encrypt(jid, text, Boolean(textarea.dataset.muc));
                    if (omemo) {
                        // TODO, disable the other risky features
                        omemo.then(omemoheader => {
                            tempId = omemoheader.tempId = Math.random().toString(36).substring(2, 15);
                            Chat.tempMessages[tempId] = text;

                            xhr = Chat_ajaxHttpDaemonSendMessage(jid, tempId, muc, null, replyMid, mucReceipts, omemoheader);

                            xhr.timeout = timeout;
                            xhr.ontimeout = onTimeout;
                            xhr.onreadystatechange = onReadyStateChange;
                        });
                    }
                } else {
                    xhr = Chat_ajaxHttpDaemonSendMessage(jid, text, muc, null, replyMid, mucReceipts);
                    xhr.timeout = timeout;
                    xhr.ontimeout = onTimeout;
                    xhr.onreadystatechange = onReadyStateChange;
                }
            }
        }
    },
    sentId: function(tempId, id)
    {
        if (Chat.tempMessages[tempId]) {
            ChatOmemoDB.putMessage(id, Chat.tempMessages[tempId]);
            delete Chat.tempMessages[tempId];
        }
    },
    setGroupChatMembers: function(members)
    {
        Chat.groupChatMembers = members;
    },

    setBundlesIds: function(jid, bundlesIds)
    {
        var store = new ChatOmemoStorage();

        let build = false;
        for (const jid in bundlesIds) {
            let storedSessionsIds = store.getSessionsIds(jid);

            // We need to build new sessions
            if (!bundlesIds[jid].every(bundleId => storedSessionsIds.includes(bundleId.toString()))) {
                build = true;
                break;
            }

            // We need to close a few sessions
            if (storedSessionsIds.length > bundlesIds[jid].length) {
                storedSessionsIds.forEach(storedSessionsId => {
                    if (!bundlesIds[jid].includes(parseInt(storedSessionsId))) {
                        var address = new libsignal.SignalProtocolAddress(jid, storedSessionsId);
                        store.removeSession(address);
                    }
                });
            }
        }

        ChatOmemo.getContactState(jid).then(enabled => {
            let state = 'no';

            if (enabled) {
                if (Object.keys(bundlesIds).length > 0) {
                    state = build
                        ? 'build'
                        : 'yes';
                }
            } else {
                if (Object.keys(bundlesIds).length > 0) {
                    state = 'disabled';
                }
            }

            Chat.setOmemoState(state);
        });
    },

    setOmemoState: function(state)
    {
        let textarea = Chat.getTextarea();
        if (textarea) {
            textarea.dataset.encryptedstate = state;
        }
    },

    enableSending: function()
    {
        Chat.sent = true;
        var send = document.querySelector('.chat_box');
        if (send) {
            send.classList.add('sending');
            send.classList.remove('finished');
        }
    },
    disableSending: function()
    {
        Chat.sent = false;
        var send = document.querySelector('.chat_box');
        if (send) {
            send.classList.remove('sending');
        }
    },
    finishedSending: function()
    {
        Chat.sent = false;
        var send = document.querySelector('.chat_box');
        if (send) send.classList.add('finished');
    },

    sentMessage: function()
    {
        Chat.disableSending();

        var textarea = Chat.getTextarea();
        localStorage.removeItem(textarea.dataset.jid + '_message');
        Chat.clearReplace();
        Chat.toggleAction();
    },
    failedMessage: function()
    {
        Toast.send(Chat.delivery_error);
        Chat.disableSending();
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

        if (textarea.value != ''
        || (Boolean(textarea.dataset.muc) && Boolean(textarea.dataset.mucGroup) == false)) {
            return;
        }

        if (!Chat.isEncrypted()) {
            Chat_ajaxLast(textarea.dataset.jid, Boolean(textarea.dataset.muc));
        } else {
            Toast.send(Chat.action_impossible_encrypted_error);
        }
    },
    /*editMessage: function(mid)
    {
        var textarea = Chat.getTextarea();
        if (textarea.value == ''
        && Boolean(textarea.dataset.muc) == false) {
            Chat_ajaxEdit(mid);
        }
    },*/
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
        Chat.sent = false;
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

            if (event.keyCode == 38 && !Chat.isEncrypted()) {
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

                if ((isTouch && !event.shiftKey)
                || (!isTouch && event.shiftKey)) {
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

        textarea.oninput = function() {
            localStorage.setItem(this.dataset.jid + '_message', this.value);

            // A little timeout to not spam the server with composing states
            setTimeout(function()
            {
                if (Chat.since + 3000 < new Date().getTime()) {
                    Chat.composing = false;
                }
            }, 3000);

            MovimUtils.textareaAutoheight(this);
            Chat.checkEmojis(this.value);
            Chat.scrollRestore();
            Chat.toggleAction();
        }

        textarea.addEventListener('paste', function(e) {
            let url;
            let clipboardData = e.clipboardData || window.clipboardData;
            let pastedData = clipboardData.getData('Text');

            try {
                url = new URL(pastedData);
            } catch (_) {
                return false;
            }

            if ((url.protocol === "http:" || url.protocol === "https:")
            && textarea.value == '' && !Chat.isEncrypted()) {
                Chat.enableSending();

                xhr = ChatActions_ajaxHttpResolveUrl(pastedData);
                xhr.timeout = 5000;
                xhr.ontimeout = function() {
                    Chat.disableSending();
                    Chat.finishedSending();
                };
                xhr.onreadystatechange = function() {
                    if (this.readyState == 4) {
                        Chat.disableSending();
                        Chat.finishedSending();
                    }
                };
            }

            Chat.toggleAction();
        });

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
            .filter(key => key.indexOf('type') == -1)
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

        if (currentEmoji) {
            currentEmoji.classList.remove('selected');

            if(currentEmoji.nextSibling) {
                currentEmoji.nextSibling.classList.add('selected');
            } else {
                document.querySelector('.chat_box .emojis img:first-child').classList.add('selected');
            }

            return true;
        }

        return false;
    },
    tryPreviousEmoji: function()
    {
        var currentEmoji = document.querySelector('.chat_box .emojis img.selected');

        if (currentEmoji) {
            currentEmoji.classList.remove('selected');

            if (currentEmoji.previousSibling) {
                currentEmoji.previousSibling.classList.add('selected');
            } else {
                document.querySelector('.chat_box .emojis img:last-child').classList.add('selected');
            }

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
    setConfig(pagination, delivery_error, action_impossible_encrypted_error)
    {
        Chat.pagination = pagination;
        Chat.delivery_error = delivery_error;
        Chat.action_impossible_encrypted_error = action_impossible_encrypted_error;
    },
    setGeneralElements(date, separator)
    {
        var div = document.createElement('div');

        Chat.currentDateTime = null;

        div.innerHTML = date;
        Chat.date = div.firstChild.cloneNode(true);
        div.innerHTML = separator;
        Chat.separator = div.firstChild.cloneNode(true);
    },
    setSpecificElements : function(left, right)
    {
        var div = document.createElement('div');

        Chat.currentDateTime = null;

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
                    Chat.currentDateTime,
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
    setObservers : function ()
    {
        var options = {
            root: Chat.getDiscussion(),
            rootMargin: '0px',
            threshold: 1.0
        };
        Chat.discussionObserver = new IntersectionObserver((entries) => {
            entries.forEach((entrie) => {
                if (entrie.target.classList.contains('gif') && entrie.isIntersecting == true) {
                    entrie.target.play();
                } else {
                    entrie.target.pause();
                }
            });
        }, options);
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

        var button = document.querySelector('#chat_widget #scroll_down.button.action');

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
    setVideoObserverBehaviour : function()
    {
        document.querySelectorAll('.file video').forEach((video) => {
            Chat.discussionObserver.observe(video);
        });
    },
    setReplyButtonBehaviour : function()
    {
        let replies = document.querySelectorAll('#chat_widget span.reply');
        let i = 0;

        while (i < replies.length) {
            replies[i].onclick = function() {
                if (this.dataset.mid) {
                    Chat_ajaxHttpDaemonReply(this.dataset.mid);
                }
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
    appendMessagesWrapper : async function(page, prepend)
    {
        var discussion = Chat.getDiscussion();

        if (page && Chat.checkDiscussion(page)) {
            if (discussion == null) return;

            Chat.setScroll();

            // Get all the messages keys
            var ids = [];
            Object.values(page).forEach(pageByDate => {
                Object.values(pageByDate).map(message => {
                    if (message.omemoheader) ids.push(message.id)
                });
            });

            // Try to preload the OMEMO messages from the cache
            if (ids.length > 2) {
                await ChatOmemoDB.loadMessagesByIds(ids);
            }

            for(date in page) {
                let messageDateTime = page[date][Object.keys(page[date])[0]].published;

                /**
                 * We might have old messages reacted pushed by the server
                 */
                if (Chat.currentDateTime
                && Chat.currentDateTime > messageDateTime
                && !prepend) {
                    return;
                }

                if (prepend === undefined || prepend === false) {
                    Chat.appendDate(date, prepend);
                }

                for(speakertime in page[date]) {
                    if (!Chat.currentDateTime) {
                        Chat.currentDateTime = page[date][speakertime].published;
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
                    lastMessage.id.substr(2) // 'id' is appended to the id
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
        Chat.setVideoObserverBehaviour();
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
            && Chat.jingleTypes.indexOf(data.type) < 0
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
            msg.classList.add('encrypted');
            p.classList.add('encrypted');
            span.appendChild(Chat.getEncryptedIcoHtml());
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
            msg.setAttribute('id', 'id' + data.id);
        }

        if (data.originid != null) {
            msg.dataset.originid = 'oid-' + MovimUtils.hash(data.originid + data.jidfrom);
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
            // OMEMO handling
            if (data.omemoheader) {
                p.innerHTML = data.omemoheader.payload.substr(0, data.omemoheader.payload.length/2);
                ChatOmemo.decrypt(data).then(plaintext => {
                    let refreshP = document.querySelector('#id' + data.id + ' p.encrypted');
                    if (refreshP) {
                        if (plaintext) {
                            let linkified = MovimUtils.linkify(plaintext);
                            refreshP.innerHTML = ChatOmemo.searchEncryptedFile(linkified);
                            refreshP.classList.remove('encrypted');
                        } else {
                            refreshP.classList.add('error');
                        }
                    }
                });
            } else {
                p.innerHTML = data.body;
            }
        }

        if (data.file != null && data.card === undefined && data.file.type !== 'xmpp') {
            bubble.querySelector('div.bubble').classList.add('file');

            // Ugly fix to clear the paragraph if the file contains a similar link
            if (p.querySelector('a') && p.querySelector('a').href == data.file.uri) {
                p.innerHTML = '';
            }

            p.appendChild(Chat.getFileHtml(data.file, data.sticker));
        }

        if (data.replaceid) {
            span.appendChild(Chat.getEditedIcoHtml());
        }

        if (data.user_id == data.jidfrom || (data.type == 'groupchat' && data.mine)) {
            if (data.displayed) {
                span.appendChild(Chat.getDisplayedIcoHtml(data.displayed));
            } else if (data.delivered) {
                span.appendChild(Chat.getDeliveredIcoHtml(data.delivered));
            }
        }

        if (data.reactionsHtml !== undefined) {
            reactions.innerHTML = data.reactionsHtml;
        }

        if (isMuc) {
            var resourceSpan = document.createElement('span');
            resourceSpan.classList.add('resource');
            resourceSpan.classList.add(data.color);
            resourceSpan.innerText = data.resource;

            msg.appendChild(resourceSpan);
        }

        if (data.published) {
            span.title = data.published;
        }

        if (data.card) {
            bubble.querySelector('div.bubble').classList.add('file');
            msg.appendChild(Chat.getCardHtml(data.card));
        }

        // Parent
        if (data.parent) {
            msg.appendChild(Chat.getParentHtml(data.parent));
        } else if (data.parentQuote) {
            msg.appendChild(Chat.getSimpleParentHtml(data.parentQuote));
        }

        msg.appendChild(p);
        msg.appendChild(span);
        msg.appendChild(reactions);

        var textarea = Chat.getTextarea();

        reaction.dataset.mid = data.mid;
        msg.appendChild(reaction);

        if ((data.id !== null && data.id.substr(0, 2) != 'm_' && reply)
         || (data.originid !== null && (Boolean(textarea.dataset.muc) == false || Boolean(textarea.dataset.mucGroup) == true))) {
            reply.dataset.mid = data.mid;
            msg.appendChild(reply);
        }

        if (actions) {
            actions.dataset.mid = data.mid;
            msg.appendChild(actions);
        }

        var elem;

        if (data.replaceid && (Boolean(textarea.dataset.muc) == false || Boolean(textarea.dataset.mucGroup) == true)) {
            elem = document.querySelector("[data-originid=oid-" + MovimUtils.hash(data.replaceid + data.jidfrom) + "]");
            msg.dataset.originid = 'oid-' + MovimUtils.hash(data.replaceid + data.jidfrom);
        }

        if (!elem) {
            elem = document.getElementById('id' + data.id);
        }

        if (elem) {
            elem.parentElement.replaceChild(msg, elem);
            mergeMsg = true;

            // If the previous message was not a file or card and is replaced by it
            if (data.file != null || data.card != null) {
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
            Chat.currentDateTime = data.published;

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
        dateNode.dataset.value = date.trim();
        dateNode.querySelector('p').innerHTML = date;
        dateNode.id = MovimUtils.cleanupId(date);

        var dates = list.querySelectorAll('li.date');

        if (prepend) {
            // If the date was already displayed we remove it
            if (dates.length > 0
            && dates[0].dataset.value == date) {
                dates[0].remove();
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
            img.setAttribute('onclick', 'Preview_ajaxHttpShow("' + sticker.url + '")');
        }

        return img;
    },
    getCardHtml: function(card) {
        var ul = document.createElement('ul');
        ul.setAttribute('class', 'card list middle noanim shadow');
        ul.innerHTML = card;

        if (ul.querySelector('li').getAttribute('onclick')) {
            ul.classList.add('active');
        }

        return ul;
    },
    getFileHtml: function(file, sticker) {
        var div = document.createElement('div');
        div.setAttribute('class', 'file');

        if (file.name) {
            if (file.type == 'video/webm' || file.type == 'video/mp4') {
                var video = document.createElement('video');
                video.setAttribute('src', file.uri);
                video.setAttribute('loop', 'loop');

                if (file.thumbnail && Object.keys(file.thumbnail).length !== 0) {
                    video.setAttribute('poster', file.thumbnail.uri);
                    video.setAttribute('width', file.thumbnail.width);
                    video.setAttribute('height', file.thumbnail.height);
                } else {
                    video.setAttribute('poster', BASE_URI + 'theme/img/poster.svg');
                }

                // Tenor implementation
                if (file.host && file.host == 'media.tenor.com') {
                    video.classList.add('gif');
                } else {
                    video.setAttribute('controls', 'controls');
                    video.setAttribute('preload', 'metadata');
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
    getEncryptedIcoHtml: function() {
        var i = document.createElement('i');
        i.className = 'material-icons';
        i.innerText = 'lock';
        return i;
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
    getSimpleParentHtml: function(parentQuote) {
        var div = document.createElement('div');
        div.classList.add('parent');

        var p = document.createElement('p');
        p.innerHTML = parentQuote;
        div.appendChild(p);

        return div;
    },
    getParentHtml: function(parent) {
        var div = document.createElement('div');
        div.classList.add('parent');
        div.dataset.parentReplaceId = parent.replaceid
        div.dataset.parentId = parent.id;
        div.id = 'parent' + parent.id;

        var span = document.createElement('span');

        if (parent.color) {
            span.classList.add('resource');
            span.classList.add(parent.color);
        }

        span.classList.add('from');
        span.innerHTML = parent.fromName;
        div.appendChild(span);

        var p = document.createElement('p');
        div.appendChild(p);

        if (parent.omemoheader) {
            p.innerHTML = parent.omemoheader.payload.substr(0, parent.omemoheader.payload.length/2);
            ChatOmemo.decrypt(parent).then(plaintext => {
                let refreshP = document.querySelector('#parent' + parent.id + ' p');
                if (refreshP) {
                    if (plaintext) {
                        refreshP.innerHTML = plaintext;
                    } else {
                        refreshP.classList.add('error');
                    }
                }
            });
        } else {
            p.innerHTML = parent.body;
        }

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
    isEncrypted: function() {
        return (Chat.getTextarea() && Chat.getTextarea().dataset.encryptedstate == 'yes');
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
                Chat.get(null, true);
            }
            chat.style.transform = '';
            Chat.slideAuthorized = false;
            Chat.startX = Chat.translateX = Chat.startY = Chat.translateY = 0;
        }, true);
    },
    isValidHttpUrl: function(string) {
        let url;

        try {
          url = new URL(string);
        } catch (_) {
          return false;
        }

        return url.protocol === "http:" || url.protocol === "https:";
    }
};

MovimWebsocket.attach(function() {
    Chat_ajaxInit();

    var jid = MovimUtils.urlParts().params[0];
    var room = (MovimUtils.urlParts().params[1] === 'room');
    if (jid) {
        if (Boolean(document.getElementById(MovimUtils.cleanupId(jid) + '-conversation'))) {
            Chat_ajaxGetHistory(jid, Chat.currentDateTime, room, false);
        } else {
            if (room) {
                Chat.getRoom(jid);
            } else {
                Chat.get(jid);
            }
        }
    } else {
        if (!MovimUtils.isMobile()) {
            Chat_ajaxHttpGetEmpty();
        }

        Notification.current('chat');
    }
});

if (typeof Upload != 'undefined') {
    Upload.attach(function(file) {
        Chat_ajaxHttpDaemonSendMessage(
            Chat.getTextarea().dataset.jid,
            false,
            Boolean(Chat.getTextarea().dataset.muc),
            file
        );
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
    if (MovimUtils.isMobile()) Chat.touchEvents();

    // Really early panel showing in case we have a JID
    var jid = MovimUtils.urlParts().params[0];
    if (jid) {
        MovimTpl.showPanel();
    }
});

var state = 0;
