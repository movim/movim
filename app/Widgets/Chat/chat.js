var Chat = {
    left: null,
    right: null,
    date: null,
    separator: null,

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

    // Timer
    typingTimer: null,

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
    jingleTypes: ['jingle_incoming', 'jingle_outgoing', 'jingle_end', 'muji_propose', 'muji_retract'],

    // Keep track of replaced messages hash when loading history or refreshing
    replacedHash: [],

    init: function (date, separator, config) {
        var div = document.createElement('div');

        div.innerHTML = date;
        Chat.date = div.firstChild.cloneNode(true);
        div.innerHTML = separator;
        Chat.separator = div.firstChild.cloneNode(true);

        Chat.pagination = config['pagination'];
        Chat.delivery_error = config['delivery_error'];
        Chat.action_impossible_encrypted_error = config['action_impossible_encrypted_error'];
    },
    autocomplete: function (event, jid) {
        RoomsUtils_ajaxMucUsersAutocomplete(jid);
    },
    onAutocomplete: function (usersList) {
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
            if (Chat.lastAutocomplete == null
                || last != Chat.lastAutocomplete.toLowerCase()) {
                Chat.searchAutocomplete = last;
                Chat.lastAutocomplete = null;
            }

            var start = (typeof Chat.lastAutocomplete === 'string')
                ? usersList.indexOf(Chat.lastAutocomplete) + 1
                : start = 0;

            var end = false;

            for (var i = start; i < usersList.length; i++) {
                if (Chat.searchAutocomplete == usersList[i].substring(0, Chat.searchAutocomplete.length).toLowerCase()) {
                    textarea.value = textarea.value.trim().slice(0, -last.length) + usersList[i] + ' ';
                    Chat.lastAutocomplete = usersList[i];
                    end = true;
                    break;
                }
            }

            if (end == false) {
                var found = usersList.find((user) => Chat.searchAutocomplete == user.substring(0, Chat.searchAutocomplete.length).toLowerCase());
                if (found) {
                    textarea.value = textarea.value.trim().slice(0, -last.length) + found + ' ';
                    Chat.lastAutocomplete = found;
                }
            }
        }
    },
    quoteMUC: function (nickname, add) {
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
    insertAtCursor: function (textToInsert) {
        textarea = Chat.getTextarea();

        const value = textarea.value;
        const start = textarea.selectionStart;
        const end = textarea.selectionEnd;

        textarea.value = value.slice(0, start) + textToInsert + value.slice(end);
        textarea.selectionStart = textarea.selectionEnd = start + textToInsert.length;

        textarea.focus();
        Chat.toggleAction();
    },
    get: function (jid, light) {
        if (jid != undefined) {
            MovimTpl.showPanel();
            document.querySelector('#chat_widget').innerHTML = '';
            Chats.setActive(jid);
        } else {
            Chats.clearAllActives();
            Rooms.clearAllActives();
        }

        Chat_ajaxGet(jid, light);
    },
    getRoom: function (jid) {
        MovimTpl.showPanel();
        document.querySelector('#chat_widget').innerHTML = '';
        Rooms.setActive(jid);

        Chat_ajaxGetRoom(jid);
    },
    getHistory: function (tryMam) {
        var textarea = Chat.getTextarea();

        let firstMessage = Chat.getDiscussion().querySelector('.message');
        if (textarea) {
            Chat_ajaxGetHistory(
                textarea.dataset.jid,
                firstMessage ? firstMessage.dataset.published : null,
                Boolean(textarea.dataset.muc),
                true,
                tryMam);
        }
    },
    sendMessage: function () {
        var textarea = Chat.getTextarea();
        var text = textarea.value;

        var isMuc = Boolean(textarea.dataset.muc);
        var mucReceipts = false;
        var jid = textarea.dataset.jid;

        if (isMuc) {
            var counter = document.querySelector('#chat_widget header span.counter');
            mucReceipts = (counter && Boolean(counter.dataset.mucreceipts));
        }

        Chat.removeSeparator();
        Chat.scrollTotally();

        document.querySelector('#embed').innerHTML = '';

        // In case it was in edit mode
        textarea.parentNode.parentNode.parentNode.classList.remove('edit');

        textarea.focus();

        if (!Chat.sent) {
            Chat.enableSending();

            if (Chat.edit) {
                Chat.edit = false;

                if (text == '') {
                    Chat.disableSending();
                } else {
                    let correct = Chat_ajaxHttpDaemonCorrect(jid, textarea.dataset.mid, text);
                    correct.then(e => {
                        Chat.sentMessage();
                    }).catch(e => {
                        Chat.failedMessage(text);
                    });
                }

                delete textarea.dataset.mid;
            } else {
                var reply = document.querySelector('#reply > div');
                replyMid = false;

                if (reply) {
                    replyMid = reply.dataset.mid;
                    Chat_ajaxClearReply();
                };

                if (Chat.getOmemoState() == 'build') {
                    var store = new ChatOmemoStorage();
                    store.getLocalRegistrationId().then(deviceId => {
                        if (deviceId) {
                            if (isMuc) {
                                Chat.checkOMEMOState(jid, isMuc);
                            } else if (store.getSessionsIds(jid).length == 0) {
                                ChatOmemo_ajaxGetDevicesList(jid);
                            }
                        } else {
                            Chat.disableSending();
                            ChatOmemo.refreshBundle(store.getOwnSessionsIds());
                        }
                    });
                } else if (Chat.getOmemoState() == 'yes') {
                    if (!text) {
                        Chat.disableSending();
                        return;
                    }

                    // Try to encrypt the message
                    let omemo = ChatOmemo.encrypt(jid, text, isMuc);
                    if (omemo) {
                        // TODO, disable the other risky features
                        omemo.then(omemoheader => {
                            tempId = omemoheader.tempId = Math.random().toString(36).substring(2, 15);
                            Chat.tempMessages[tempId] = text;

                            let request = Chat_ajaxHttpDaemonSendMessage(jid, tempId, isMuc, null, replyMid, mucReceipts, omemoheader);
                            request.then(e => {
                                Chat.sentMessage();
                            }).catch(e => {
                                Chat.failedMessage();
                            });
                        });
                    }
                } else {
                    let request = Chat_ajaxHttpDaemonSendMessage(jid, text, isMuc, null, replyMid, mucReceipts);
                    request.then(e => {
                        Chat.sentMessage();
                    }).catch(e => {
                        Chat.failedMessage(text);
                    });
                }
            }
        }
    },
    sentId: function (tempId, id) {
        if (Chat.tempMessages[tempId]) {
            ChatOmemoDB.putMessage(id, Chat.tempMessages[tempId]);
            delete Chat.tempMessages[tempId];
        }
    },
    setGroupChatMembers: function (members) {
        Chat.groupChatMembers = members;
    },

    checkOMEMOState: function (jid, muc) {
        var store = new ChatOmemoStorage();

        let build = muc
            ? Chat.groupChatMembers.some(
                member => !store.isJidResolved(member)
            )
            : !store.isJidResolved(jid);

        ChatOmemo.getContactState(jid).then(enabled => {
            let state = 'no';

            if (enabled) {
                state = build
                    ? 'build'
                    : 'yes';
            } else {
                state = 'disabled';
            }

            Chat.setOmemoState(state);
        });
    },

    setOmemoState: function (state) {
        let textarea = Chat.getTextarea();
        if (textarea) {
            textarea.dataset.encryptedstate = state;
        }
    },

    getOmemoState: function () {
        let textarea = Chat.getTextarea();
        if (textarea) {
            return textarea.dataset.encryptedstate;
        }
    },

    enableSending: function () {
        Chat.sent = true;
        var send = document.querySelector('.chat_box');
        if (send) {
            send.classList.add('sending');
            send.classList.remove('finished');
        }
    },
    disableSending: function () {
        Chat.sent = false;
        var send = document.querySelector('.chat_box');
        if (send) {
            send.classList.remove('sending');
        }
    },
    finishedSending: function () {
        Chat.sent = false;
        var send = document.querySelector('.chat_box');
        if (send) send.classList.add('finished');
    },

    sentMessage: function () {
        Chat.disableSending();

        var textarea = Chat.getTextarea();
        localStorage.removeItem(textarea.dataset.jid + '_message');
        Chat.clearReplace();
        Chat.toggleAction();
    },
    failedMessage: function (text) {
        Toast.send(Chat.delivery_error);
        Chat.disableSending();

        // We try to put back the text in place
        var textarea = Chat.getTextarea();
        if (textarea.value == '') {
            textarea.value = text;
        }
    },
    clearReplace: function () {
        Chat.edit = false;
        var textarea = Chat.getTextarea();
        textarea.value = localStorage.getItem(textarea.dataset.jid + '_message');
        MovimUtils.textareaAutoheight(textarea);
    },
    editPrevious: function () {
        var textarea = Chat.getTextarea();

        if (textarea.value != '') {
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
        && !Boolean(textarea.dataset.muc)) {
            Chat_ajaxEdit(mid);
        }
    },*/
    resolveMessage: function (mid) {
        ChatActions_ajaxHttpResolveMessage(mid);
    },
    refreshMessage: function (mid) {
        Chat_ajaxRefreshMessage(mid);
    },
    focus: function () {
        Chat.sent = false;
        Chat.composing = false;
        Chat.clearReplace();
        Chat.toggleAction();

        var textarea = Chat.getTextarea();
        textarea.onkeydown = function (event) {
            if ((event.key == 'ArrowLeft' && Chat.tryPreviousEmoji())
                || (event.key == 'ArrowRight' && Chat.tryNextEmoji())) {
                event.preventDefault();
                return;
            }

            if (this.dataset.muc
                && event.key == 'Tab') {
                event.preventDefault();
                if (Chat.autocompleteList == null) {
                    Chat.autocomplete(event, this.dataset.jid);
                } else {
                    Chat.onAutocomplete(Chat.autocompleteList);
                }
                return;
            }

            if (event.key == 'ArrowUp' && !Chat.isEncrypted()) {
                Chat.editPrevious();
            } else if (event.key == 'Escape'
                && (this.value == '' || Chat.edit == true)) {
                localStorage.removeItem(textarea.dataset.jid + '_message');
                textarea.parentNode.parentNode.parentNode.classList.remove('edit');
                Chat.clearReplace();
            }
        };

        textarea.onkeypress = function (event) {
            if (event.key == 'Enter') {
                // An emoji was selected
                var emoji = document.querySelector('.chat_box .emojis img.selected');
                if (emoji) {
                    Chat.selectEmoji(emoji);
                    event.preventDefault();
                    return;
                }

                if ((MovimEvents.isTouch && !event.shiftKey)
                    || (!MovimEvents.isTouch && event.shiftKey)) {
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

        textarea.oninput = function () {
            localStorage.setItem(this.dataset.jid + '_message', this.value);

            // A little timeout to not spam the server with composing states
            setTimeout(function () {
                if (Chat.since + 3000 < new Date().getTime()) {
                    Chat.composing = false;
                }
            }, 3000);

            if (Chat.typingTimer != null) clearTimeout(Chat.typingTimer);
            if (this.value) {
                if (Chat.lastResolvedUrl != this.value) Chat.lastResolvedUrl = null;
                Chat.typingTimer = setTimeout(e => Chat.checkResolveUrl(this.value), 1000);
            }

            MovimUtils.textareaAutoheight(this);
            Chat.checkEmojis(this.value);
            Chat.scrollRestore();
            Chat.toggleAction();

            document.querySelector('#embed').innerHTML = '';
        }

        textarea.addEventListener('paste', function (e) {
            clearTimeout(Chat.typingTimer);

            let clipboardData = e.clipboardData || window.clipboardData;
            let pastedData = clipboardData.getData('Text');

            Chat.checkResolveUrl(pastedData);
            Chat.toggleAction();
        });

        if (document.documentElement.clientWidth > 1024) {
            textarea.focus();
        }

        Chat.autocompleteList = null;
    },
    checkResolveUrl(content) {
        let url;

        try {
            url = new URL(content);
        } catch (_) {
            return false;
        }

        if ((url.protocol === "http:" || url.protocol === "https:")
            && !Chat.isEncrypted()
            && !content.includes(' ')
            && Chat.lastResolvedUrl != content) {
            Chat.enableSending();

            let resolve = ChatActions_ajaxHttpResolveUrl(content);
            resolve.then(e => {
                Chat.disableSending();
                Chat.finishedSending();
            }).catch(e => {
                Chat.disableSending();
                Chat.finishedSending();
            });

            Chat.lastResolvedUrl = content;
        }
    },
    checkEmojis: function (value, reaction, noColon) {
        value = value.toLowerCase();

        listSelector = reaction
            ? '.emojis_grid.results'
            : '.chat_box .emojis';

        var emojisList = document.querySelector(listSelector);
        emojisList.innerHTML = '';

        if (!value) return;

        if (noColon || value.lastIndexOf(':') > -1 && value.length > value.lastIndexOf(':') + 2) {
            var first = true;

            Object.keys(window.favoriteEmojis)
                .filter(key => key.includes(value.substring(value.lastIndexOf(':') + 1)) && !key.includes('type'))
                .slice(0, 20)
                .forEach(found => {
                    var img = document.createElement('img');
                    img.setAttribute('src', favoriteEmojis[found]);
                    img.classList.add('emoji');
                    if (reaction) img.classList.add('large');

                    if (first) {
                        img.classList.add('selected');
                        first = false;
                    }

                    img.title = ':' + found + ':';
                    img.dataset.emoji = ':' + found + ':';

                    if (!reaction) {
                        img.addEventListener('click', e => {
                            Chat.selectEmoji(e.target);
                        });
                    }

                    emojisList.appendChild(img);
                });

            Object.keys(window.emojis)
                .filter(key => key.includes(value.substring(value.lastIndexOf(':') + 1)) && !key.includes('type'))
                .slice(0, 30)
                .forEach(found => {
                    var img = document.createElement('img');
                    img.setAttribute('src', BASE_URI + '/theme/img/emojis/svg/' + emojis[found].c + '.svg');
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
    selectEmoji: function (emoji) {
        var emojisList = document.querySelector('.chat_box .emojis');
        var textarea = Chat.getTextarea();

        textarea.value = textarea.value.substring(0, textarea.value.lastIndexOf(':'));
        emojisList.innerHTML = '';
        Chat.insertAtCursor(emoji.dataset.emoji + ' ');
    },
    tryNextEmoji: function () {
        var currentEmoji = document.querySelector('.chat_box .emojis img.selected');

        if (currentEmoji) {
            currentEmoji.classList.remove('selected');

            if (currentEmoji.nextSibling) {
                currentEmoji.nextSibling.classList.add('selected');
            } else {
                document.querySelector('.chat_box .emojis img:first-child').classList.add('selected');
            }

            return true;
        }

        return false;
    },
    tryPreviousEmoji: function () {
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
    setTextarea: function (value, mid) {
        Chat.edit = true;
        var textarea = Chat.getTextarea();
        textarea.value = value;
        textarea.parentNode.parentNode.parentNode.classList.add('edit');

        if (mid) {
            textarea.dataset.mid = mid;
        }

        MovimUtils.textareaAutoheight(textarea);
        textarea.focus();
    },
    setConfig(pagination, delivery_error, action_impossible_encrypted_error) {
        Chat.pagination = pagination;
        Chat.delivery_error = delivery_error;
        Chat.action_impossible_encrypted_error = action_impossible_encrypted_error;
    },
    setSpecificElements: function (left, right) {
        var div = document.createElement('div');

        div.innerHTML = left;
        Chat.left = div.firstChild.cloneNode(true);
        div.innerHTML = right;
        Chat.right = div.firstChild.cloneNode(true);
    },
    setScrollBehaviour: function () {
        var discussion = Chat.getDiscussion();
        if (discussion == null) return;

        discussion.onscroll = function () {
            if (this.scrollTop < 1) {
                Chat.getHistory(true);
            }

            Chat.setScroll();
        };

        Chat.setScroll();
    },
    setScroll: function () {
        var discussion = Chat.getDiscussion();
        if (discussion == null) return;

        Chat.lastHeight = discussion.scrollHeight;
        Chat.lastScroll = discussion.scrollTop + discussion.clientHeight;

        var button = document.querySelector('#chat_widget #scroll_down.button.action');

        if (Chat.isScrolled()) {
            button.classList.remove('show');
        } else {
            button.classList.add('show');
        }
    },
    isScrolled: function () {
        return Chat.lastHeight - 5 <= Chat.lastScroll;
    },
    scrollRestore: function () {
        var discussion = Chat.getDiscussion();
        if (!discussion) return;

        if (Chat.isScrolled()) {
            Chat.scrollTotally();
        } else {
            discussion.scrollTop = Chat.lastScroll - discussion.clientHeight;
        }
    },
    scrollTotally: function () {
        var discussion = Chat.getDiscussion();
        if (discussion == null) return;

        discussion.scrollTop = discussion.scrollHeight;
    },
    setObservers: function () {
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
    scrollToSeparator: function () {
        var discussion = Chat.getDiscussion();
        if (discussion == null) return;

        var separator = discussion.querySelector('.separator');
        if (separator) {
            discussion.scrollTop = separator.offsetTop - 65;
            Chat.setScroll();
        }
    },
    removeSeparator: function () {
        var separator = Chat.getDiscussion().querySelector('li.separator');
        if (separator) separator.remove();
    },
    setReactionButtonBehaviour: function () {
        let reactions = document.querySelectorAll('#chat_widget span.reaction');
        let i = 0;

        while (i < reactions.length) {
            reactions[i].onclick = function (e) {
                e.stopPropagation();
                Stickers_ajaxReaction(this.dataset.mid);
            }

            i++;
        }
    },
    setParentScrollBehaviour: function () {
        let toParents = document.querySelectorAll('#chat_widget div.parent');
        let i = 0;

        while (i < toParents.length) {
            toParents[i].onclick = function (e) {
                var parentMsg = document.getElementById('id' + this.dataset.parentId);
                if (!parentMsg) {
                    parentMsg = document.getElementById('id' + this.dataset.parentReplaceId)
                }
                if (parentMsg) {
                    Chat.scrollAndBlinkMessage(parentMsg)
                }
            }

            i++;
        }
    },
    scrollAndBlinkMessage: function (msg) {
        scrollToLi = msg.parentNode.parentNode;

        msg.parentNode.classList.add('scroll_blink');

        setTimeout(() => {
            msg.parentNode.classList.remove('scroll_blink');
        }, 1000);

        document.querySelector('#chat_widget .contained').scrollTo({
            top: scrollToLi.offsetTop - 160,
            left: 0
        });
    },
    scrollAndBlinkMessageMid: function (mid) {
        setTimeout(() => {
            Chat.scrollAndBlinkMessage(document.querySelector('div[data-mid="' + mid + '"'));
        }, 500)
    },
    setVideoObserverBehaviour: function () {
        document.querySelectorAll('.file video').forEach(video => {
            Chat.discussionObserver.observe(video);
        });
    },
    setAudioPlayersBehaviour: function () {
        document.querySelectorAll('.audio_player').forEach((audioPlayer, index, audioPlayers) => {
            var audio = audioPlayer.querySelector('audio');
            var buttonPlayPause = audioPlayer.querySelector('span.play_pause');
            var progressBar = audioPlayer.querySelector('input[type=range]');
            var timer = audioPlayer.querySelector('p.timer');
            let mouseDownOnSlider = false;

            audio.onloadeddata = function () {
                progressBar.value = 0;
                timer.innerHTML = MovimUtils.cleanTime(audio.currentTime) + ' / ' + MovimUtils.cleanTime(0);
            }

            audio.ontimeupdate = function () {
                if (!mouseDownOnSlider) {
                    progressBar.value = audio.currentTime / audio.duration * 100;
                    timer.innerHTML = MovimUtils.cleanTime(audio.currentTime)
                        + ' / '
                        + MovimUtils.cleanTime(Number.isFinite(audio.duration) ? audio.duration : 0);
                }
            }

            audio.onplay = function () {
                buttonPlayPause.querySelector('i').innerHTML = 'pause';
            };

            audio.onpause = function () {
                buttonPlayPause.querySelector('i').innerHTML = 'play_arrow';
            };

            audio.onended = function () {
                var maybeNext = audioPlayers[index + 1];

                if (maybeNext) {
                    maybeNext.querySelector('audio').play();
                }
            }

            progressBar.onchange = function () {
                const pct = progressBar.value / 100;
                audio.currentTime = (audio.duration || 0) * pct;
            }

            progressBar.onmousedown = function () {
                mouseDownOnSlider = true;
            }

            progressBar.onmouseup = function () {
                mouseDownOnSlider = false;
            }

            buttonPlayPause.onclick = function () {
                if (audio.paused) {
                    document.querySelectorAll('.audio_player:not(#' + audioPlayer.id + ') audio').forEach(otherAudio => otherAudio.pause());
                    audio.play();
                } else {
                    audio.pause();
                }
            };
        });
    },
    setActionsButtonBehaviour: function () {
        document.querySelectorAll('#chat_widget .contained span.reply').forEach(reply =>
            reply.onclick = function (e) {
                e.stopPropagation();
                Chat_ajaxHttpDaemonReply(this.dataset.mid);
            }
        );
    },
    setMessagePressBehaviour: function () {
        document.querySelectorAll('#chat_widget li div.bubble:not(.file) > div.message').forEach(message => {
            message.onmousedown = function (e) {
                setTimeout(() => {
                    if (e.button == 0
                        && (e.target.classList.contains('message')
                            || e.target.parentElement.classList.contains('message')) && !(window.getSelection().toString() != ''
                            )
                        && this.dataset.mid
                        && e.target.tagName.toLowerCase() != 'span') {
                        ChatActions_ajaxShowMessageDialog(this.dataset.mid);
                    }
                }, 200);
            }
        });

        document.querySelectorAll('#chat_widget li div.bubble.file > div.message').forEach(message => {
            if (card = message.querySelector('ul.list.card > li > div')) {
                card.onclick = function (e) {
                    ChatActions_ajaxShowMessageDialog(message.dataset.mid);
                };
            }
        });
    },
    checkDiscussion: function (page) {
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
    appendMessagesWrapper: async function (page, prepend) {
        var discussion = Chat.getDiscussion();

        if (page && Chat.checkDiscussion(page)) {
            if (discussion == null) return;

            discussion.querySelector('.placeholder.empty').classList.remove('show');

            Chat.setScroll();

            // Get all the messages keys
            var ids = [];
            Object.values(page).forEach(pageByDate => {
                Object.values(pageByDate).map(message => {
                    if (message.omemoheader) ids.push(message.id)
                });
            });

            // Try to preload the OMEMO messages from the cache
            if (ids.length > 2 && OMEMO_ENABLED) {
                await ChatOmemoDB.loadMessagesByIds(ids);
            }

            for (date in page) {
                let messageDateTime = page[date][Object.keys(page[date])[0]].published;

                /**
                 * We might have old messages reacted pushed by the server
                 */
                /*if (Chat.oldestMessageDateTime
                    && Chat.oldestMessageDateTime > messageDateTime
                    && !prepend) {
                    return;
                }*/

                if (prepend === undefined || prepend === false) {
                    Chat.appendDate(date, prepend);
                }

                for (speakertime in page[date]) {
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
                    lastMessage.id.substring(2) // 'id' is appended to the id
                );
            }
        } else if (discussion !== null) {
            if (discussion.querySelector('ul').innerHTML === '') {
                discussion.querySelector('ul').classList.remove('spin');
                discussion.querySelector('.placeholder.empty').classList.add('show');
            }
        }

        if (discussion !== null) {
            if (discussion.querySelectorAll('ul li:not(.oppose)').length > 0 && discussion.querySelectorAll('ul li.oppose').length == 0
                && discussion.querySelectorAll('ul li').length < 5) {
                discussion.querySelector('.placeholder.first_messages').classList.add('show');
            } else {
                discussion.querySelector('.placeholder.first_messages').classList.remove('show');
            }
        }

        Chat.setScrollBehaviour();
        Chat.setReactionButtonBehaviour();
        Chat.setActionsButtonBehaviour();
        Chat.setParentScrollBehaviour();
        Chat.setVideoObserverBehaviour();
        Chat.setAudioPlayersBehaviour();
        Chat.setMessagePressBehaviour();
    },
    appendMessage: function (idjidtime, data, prepend) {
        if (data.body === null) return;

        var bubble = null,
            mergeMsg = false,
            msgStack,
            refBubble;

        var textarea = Chat.getTextarea();
        var isMuc = Boolean(textarea.dataset.muc);
        var jidtime = idjidtime.substring(idjidtime.indexOf('<') + 1);

        if (prepend) {
            refBubble = document.querySelector('#chat_widget .contained section > ul > li:first-child');
            msgStack = document.querySelector("[data-bubble='" + jidtime + "']");
        } else {
            refBubble = document.querySelector("#chat_widget .contained section > ul > li:last-child");
            var stack = document.querySelectorAll("[data-bubble='" + jidtime + "']");
            msgStack = stack[stack.length - 1];
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
            if (data.user_id == data.jidfrom || data.mine) {
                bubble = Chat.right.cloneNode(true);
                id = (data.mine)
                    ? data.jidfrom
                    : data.jidto;
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

        if (refBubble && refBubble.dataset.resource == bubble.dataset.resource
            && mergeMsg == false
            && isMuc) {
            if (prepend) {
                refBubble.classList.add('sequel');
            } else {
                bubble.classList.add('sequel');
            }
        }

        // If there is already a msg in this bubble, create another div (next msg or replacement)
        var msg = (bubble.querySelector('div.bubble p')
            && bubble.querySelector('div.bubble p').innerHTML != '')
            ? Chat.right.querySelector('div.bubble > div.message').cloneNode(true)
            : bubble.querySelector('div.bubble > div.message');

        var info = msg.querySelector('span.info');
        var p = msg.getElementsByTagName('p')[0];
        var reaction = msg.querySelector('span.reaction');
        var reply = msg.querySelector('span.reply');
        var reactions = msg.querySelector('ul.reactions');

        // And we complete the message structure from the data we got...

        if (data.retracted) {
            p.classList.add('retracted');
        }

        if (data.encrypted) {
            msg.classList.add('encrypted');
            p.classList.add('encrypted');
        }

        if (data.body.match(/^\/me\s/)) {
            p.classList.add('quote');
            data.body = data.body.substring(4);
        }

        if (data.id != null) {
            msg.setAttribute('id', 'id' + data.id);
        }

        if (data.messageid != null) {
            msg.dataset.messageid = 'messageid-' + MovimUtils.hash(data.messageid + data.jidfrom);

            /**
             * If we append (during a reload) or prepend, we might have already a
             * replacement message displayed, in that case we just stop here
             */
            if (this.replacedHash.includes(msg.dataset.messageid)) return;
        }

        if (data.rtl) {
            msg.setAttribute('dir', 'rtl');
        }

        // OMEMO handling
        if (data.omemoheader && data.encrypted && !data.retracted && OMEMO_ENABLED) {
            p.innerHTML = data.omemoheader.payload.substring(0, data.omemoheader.payload.length / 2);
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

        if (data.replaceid) {
            msg.classList.add('edited');
        }

        if (data.user_id == data.jidfrom || (isMuc && data.mine)) {
            if (data.displayed) {
                msg.classList.add('displayed');
            } else if (data.delivered) {
                msg.classList.add('delivered');
            }
        }

        if (data.reactionsHtml !== undefined && data.retracted == false) {
            reactions.innerHTML = data.reactionsHtml;
        }

        if (isMuc && data.resource) {
            var resourceSpan = document.createElement('span');
            resourceSpan.classList.add('resource');
            resourceSpan.classList.add(data.color);
            resourceSpan.innerText = data.resource;

            msg.appendChild(resourceSpan);
        }

        if (data.sticker != null && data.retracted == false) {
            bubble.querySelector('div.bubble').classList.add('file');
            p.appendChild(Chat.getStickerHtml(data));

        } else if (data.file != null && data.card === undefined && data.file.type !== 'xmpp' && data.retracted == false) {
            bubble.querySelector('div.bubble').classList.add('file');
            msg.appendChild(Chat.getFileHtml(data.file, data));
        }

        if (data.published) {
            info.title = data.published;
        }

        // Parent
        if (data.parent) {
            msg.appendChild(Chat.getParentHtml(data.parent));
        } else if (data.parentQuote) {
            msg.appendChild(Chat.getSimpleParentHtml(data.parentQuote));
        }

        if (data.card) {
            bubble.querySelector('div.bubble').classList.add('file');
            msg.appendChild(Chat.getCardHtml(data.card, data.story));
        }

        msg.dataset.published = data.published;

        msg.appendChild(p);
        msg.appendChild(info);
        msg.appendChild(reactions);

        if (data.mid) {
            if ((isMuc && data.stanzaid)
                || (!isMuc && data.messageid)) {
                reaction.dataset.mid = data.mid;
                msg.appendChild(reaction);
            }

            if (reply) {
                reply.dataset.mid = data.mid;
                msg.appendChild(reply);
            }

            msg.dataset.mid = data.mid;
        }

        var elem;

        // The following commented part introduces a security issue, see https://xmpp.org/extensions/xep-0308.html#security
        if (data.replaceid /*&& (!isMuc || Boolean(textarea.dataset.mucGroup) == true)*/) {
            elem = document.querySelector("[data-messageid=messageid-" + MovimUtils.hash(data.replaceid + data.jidfrom) + "]");
            msg.dataset.messageid = 'messageid-' + MovimUtils.hash(data.replaceid + data.jidfrom);

            this.replacedHash.push(msg.dataset.messageid);
        }

        if (!elem) {
            elem = document.getElementById('id' + data.id);
        }

        if (elem) {
            elem.parentElement.replaceChild(msg, elem);
            mergeMsg = true;

            // If the previous message was not a file or card and is replaced by it
            if ((data.file != null || data.card != null) && data.retracted == false) {
                msg.parentElement.classList.add('file');
            } else {
                msg.parentElement.classList.remove('file');
            }

            if (data.sticker != null && data.retracted == false) {
                msg.parentElement.classList.add('sticker', 'file');
            } else {
                msg.parentElement.classList.remove('sticker');
            }
        } else {
            if (prepend) {
                bubble.querySelector('div.bubble').insertBefore(msg, bubble.querySelector('div.bubble').firstChild);
            } else {
                bubble.querySelector('div.bubble').appendChild(msg);
            }
        }

        // MUC specific
        if (isMuc) {
            if (data.moderator) {
                bubble.querySelector('div.bubble').classList.add('moderator');
            }

            icon = bubble.querySelector('span.primary.icon');

            if (icon.querySelector('img') == undefined) {
                if (data.icon_url) {
                    var img = document.createElement('img');
                    img.setAttribute('src', data.icon_url);
                    img.setAttribute('loading', 'lazy');
                    icon.appendChild(img);
                }

                if (data.resource) {
                    icon.dataset.resource = data.resource;
                } else {
                    icon.innerHTML = '';

                    var i = document.createElement('i');
                    i.className = 'material-symbols';
                    i.innerText = 'notes';

                    icon.appendChild(i);
                }
            }

            if (data.quoted) {
                bubble.querySelector('div.bubble').classList.add('quoted');
            }
        }

        if (!mergeMsg) {
            if (prepend) {
                MovimTpl.prepend('#' + id, bubble.outerHTML);
            } else {
                MovimTpl.append('#' + id, bubble.outerHTML);
            }
        }
    },
    appendDate: function (date, prepend) {
        var list = document.querySelector('#chat_widget > div ul.conversation');

        dateNode = Chat.date.cloneNode(true);
        dateNode.querySelector('p').innerHTML = date;
        dateNode.id = MovimUtils.cleanupId(date);

        if (prepend) {
            if (list.firstChild.className == 'date') return;

            if (existingDate = document.getElementById(MovimUtils.cleanupId(date))) {
                existingDate.remove();
            }

            list.insertBefore(dateNode, list.firstChild);
        } else {
            if (document.getElementById(MovimUtils.cleanupId(date))) {
                return;
            }

            list.appendChild(dateNode);
        }
    },
    insertSeparator: function (counter) {
        separatorNode = Chat.separator.cloneNode(true);

        var list = document.querySelector('#chat_widget > div ul.conversation');

        if (!list || list.querySelector('li.separator')) return;

        var messages = document.querySelectorAll('#chat_widget > div ul.conversation div.bubble p');

        if (messages.length > counter && counter > 0) {
            var p = messages[messages.length - counter];
            list.insertBefore(separatorNode, p.parentNode.parentNode.parentNode);
        }
    },
    getStickerHtml: function (data) {
        var img = document.createElement('img');
        img.classList.add('sticker');

        if (data.sticker.url) {
            if (data.sticker.thumb) {
                img.setAttribute('src', data.sticker.thumb);
            } else {
                img.setAttribute('src', data.sticker.url);
            }

            if (data.sticker.width) img.setAttribute('width', data.sticker.width);
            if (data.sticker.height) {
                img.setAttribute('height', data.sticker.height);
            } else {
                img.setAttribute('height', '170');
            }
        }

        return img;
    },
    getCardHtml: function (card, story) {
        var ul = document.createElement('ul');

        ul.innerHTML = card;
        ul.setAttribute('class', story
            ? 'list card shadow flex fourth gallery active'
            : 'list card middle noanim shadow active');

        if (story) {
            ul.querySelector('li').classList.add('story');
        }

        return ul;
    },
    getFileHtml: function (file, data) {
        var div = document.createElement('div');
        div.setAttribute('class', 'file');

        if (file.name) {
            div.dataset.type = file.type;

            if (file.preview) {
                var img = document.createElement('img');
                if (file.preview.url) {
                    img.setAttribute('src', file.preview.thumb ?? file.preview.url);

                    if (file.preview.width && file.preview.height) {
                        img.setAttribute('width', file.preview.width);
                        img.setAttribute('height', file.preview.height);
                    } else if (file.thumbnail_width && file.thumbnail_height) {
                        img.setAttribute('width', file.thumbnail_width);
                        img.setAttribute('height', file.thumbnail_height);
                    }
                }

                if (file.preview.title) {
                    img.title = file.preview.title;
                }

                if (file.preview.picture) {
                    img.classList.add('active');
                    img.setAttribute('onclick', 'Preview_ajaxHttpShow("' + file.preview.url + '", ' + data.mid + ')');
                }

                if (file.preview.thumbnail_type == 'image/thumbhash' && file.preview.thumbnail_url) {
                    try {
                        div.style.background = `center / cover url(${thumbHashToDataURL(MovimUtils.base64ToBinary(file.preview.thumbnail_url))})`;
                    } catch (error) {
                        console.log('Cannot handle thumbhash hash');
                    }
                }

                div.appendChild(img);
            }

            if (file.type == 'audio/ogg' || file.type == 'audio/opus' || file.type == 'audio/mpeg') {
                div.appendChild(Chat.getAudioPlayer(file));
            } else if (file.type == 'video/webm' || file.type == 'video/mp4') {
                div.appendChild(Chat.getVideoPlayer(file));
            }

            var url = new URL(file.url);

            // Tenor implementation
            if (url.host && url.host == 'media.tenor.com'
                || file.type == 'audio/ogg' || file.type == 'audio/opus' || file.type == 'audio/mpeg') {
                return div;
            }

            if (!file.preview) {
                var a = document.createElement('a');

                a.textContent = file.name;
                a.href = file.url;
                a.target = '_blank';
                a.rel = 'noopener noreferrer';

                div.appendChild(a);

                if (file.host) {
                    var host = document.createElement('span');
                    host.innerHTML = file.host;
                    host.className = 'host';
                    a.appendChild(host);
                }

                if (file.size > 0) {
                    var span = document.createElement('span');
                    span.innerHTML = file.cleansize;
                    span.className = 'size';

                    a.appendChild(span);
                }
            }
        }

        return div;
    },
    getAudioPlayer: function (file) {
        var div = document.createElement('div');
        div.setAttribute('title', file.name);
        div.classList.add('audio_player');
        div.id = 'a_' + file.message_mid;

        var audio = document.createElement('audio');
        audio.setAttribute('src', file.url + '#t=0.01');
        //audio.setAttribute('controls', '');
        div.appendChild(audio);


        var playPauseButton = document.createElement('span');
        playPauseButton.classList.add('button', 'flat', 'gray');
        playPauseButton.classList.add('play_pause');
        var i = document.createElement('i');
        i.className = 'material-symbols';
        i.innerText = 'play_arrow';
        playPauseButton.appendChild(i);

        div.appendChild(playPauseButton);

        var timer = document.createElement('p');
        timer.classList.add('timer');

        div.appendChild(timer);

        var progressBar = document.createElement('input');
        progressBar.type = 'range';
        progressBar.value = 0;
        progressBar.setAttribute('min', 0);
        progressBar.setAttribute('max', 100);
        progressBar.setAttribute('step', 0.1);

        div.appendChild(progressBar);

        var downloadButton = document.createElement('a');
        downloadButton.classList.add('button', 'flat', 'gray');
        downloadButton.href = file.url;
        downloadButton.target = '_blank';
        var i = document.createElement('i');
        i.className = 'material-symbols';
        i.innerText = 'file_download';
        downloadButton.appendChild(i);

        div.appendChild(downloadButton);

        return div;
    },
    getVideoPlayer: function (file) {
        var video = document.createElement('video');
        video.setAttribute('src', file.url + '#t=0.01');
        video.setAttribute('loop', 'loop');

        if (file.thumbnail_url != null) {
            video.setAttribute('poster', file.thumbnail_url);
            video.setAttribute('width', file.thumbnail_width);
            video.setAttribute('height', file.thumbnail_height);
        } else {
            video.setAttribute('poster', BASE_URI + 'theme/img/poster.svg');
        }

        var url = new URL(file.url);

        // Tenor implementation
        if (url.host && url.host == 'media.tenor.com') {
            video.classList.add('gif');
        } else {
            video.setAttribute('controls', 'controls');
            video.setAttribute('preload', 'metadata');
        }

        return video;
    },
    getSimpleParentHtml: function (parentQuote) {
        var div = document.createElement('div');
        div.classList.add('parent');

        var p = document.createElement('p');
        p.innerHTML = parentQuote;
        div.appendChild(p);

        return div;
    },
    getParentHtml: function (parent) {
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

        if (parent.omemoheader && OMEMO_ENABLED) {
            p.innerHTML = parent.omemoheader.payload.substring(0, parent.omemoheader.payload.length / 2);
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
    toggleAction: function () {
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
    toggleAttach: function (forceDisabled) {
        var attach = document.querySelector('#chat_widget .chat_box span.attach');

        if (attach) {
            if (forceDisabled) {
                attach.classList.remove('enabled');
            } else {
                attach.classList.toggle('enabled');
            }
        }
    },
    getTextarea: function () {
        var textarea = document.querySelector('#chat_textarea');
        if (textarea) return textarea;
    },
    isEncrypted: function () {
        return (Chat.getTextarea() && Chat.getTextarea().dataset.encryptedstate == 'yes');
    },
    getDiscussion: function () {
        return document.querySelector('#chat_widget div.contained');
    },
    touchEvents: function () {
        var chat = document.querySelector('#chat_widget');
        clientWidth = Math.abs(document.body.clientWidth);

        if (!chat) return;

        chat.addEventListener('touchstart', function (event) {
            chat.classList.remove('moving');

            Chat.startX = event.targetTouches[0].pageX;
            Chat.startY = event.targetTouches[0].pageY;
        }, true);

        chat.addEventListener('touchmove', function (event) {
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
                    chat.style.transform = 'translateX(' + (Chat.translateX - delay) + 'px)';
                }
            }
        }, true);

        chat.addEventListener('touchend', function (event) {
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
    isValidHttpUrl: function (string) {
        let url;

        try {
            url = new URL(string);
        } catch (_) {
            return false;
        }

        return url.protocol === "http:" || url.protocol === "https:";
    },
    getNewerMessages: function () {
        var jid = MovimUtils.urlParts().params[0];
        let lastMessage = Chat.getDiscussion().querySelector('li:last-child .bubble:last-child .message:last-child');

        if (jid) {
            Chat_ajaxGetHistory(
                jid,
                lastMessage ? lastMessage.dataset.published : null,
                (MovimUtils.urlParts().params[1] === 'room'),
                false,
                false
            );
        }
    },
};

MovimWebsocket.attach(function () {
    if (!Chat.pagination) {
        Chat_ajaxInit();
    }

    var jid = MovimUtils.urlParts().params[0];

    if (jid) {
        if (Boolean(document.getElementById(MovimUtils.cleanupId(jid) + '-conversation'))) {
            Chat.getNewerMessages();
        } else {
            (MovimUtils.urlParts().params[1] === 'room')
                ? Chat.getRoom(jid)
                : Chat.get(jid);
        }
    } else {
        if (!MovimUtils.isMobile()) {
            Chat_ajaxHttpGetEmpty();
        }

        Notif.current('chat');
    }
});

MovimEvents.registerWindow('focus', 'chat', () => {
    if (MovimWebsocket.connection) {
        var jid = MovimUtils.urlParts().params[0];
        if (jid) {
            Chat_ajaxGetHeader(jid, (MovimUtils.urlParts().params[1] === 'room'));
        }
    }

    var textarea = Chat.getTextarea();
    if (textarea) textarea.focus();
});

MovimEvents.registerWindow('resize', 'chat', () => Chat.scrollRestore());

MovimEvents.registerWindow('loaded', 'chat', () => {
    if (MovimUtils.isMobile()) Chat.touchEvents();

    Upload.initiate((file) => {
        if (MovimUtils.urlParts().page == 'chat'
            && (typeof (PublishStories) == 'undefined' || PublishStories.main == undefined)) {
            Upload.prependName = 'chat';
        }
    });

    Upload.attach((file) => {
        if (MovimUtils.urlParts().page == 'chat'
            && (typeof (PublishStories) == 'undefined' || PublishStories.main == undefined)) {
            Chat_ajaxHttpDaemonSendMessage(
                Chat.getTextarea().dataset.jid,
                false,
                Boolean(Chat.getTextarea().dataset.muc),
                file
            );
        }
    });

    // Really early panel showing in case we have a JID
    var parts = MovimUtils.urlParts();
    if (parts.page == 'chat' && parts.params[0]) {
        MovimTpl.showPanel();
    }
});

var state = 0;
