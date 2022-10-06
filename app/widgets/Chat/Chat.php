<?php

use Moxl\Xec\Action\Message\Publish;
use Moxl\Xec\Action\Message\Reactions;

use Moxl\Xec\Action\Muc\GetConfig;
use Moxl\Xec\Action\Muc\SetConfig;

use App\Contact;
use App\Message;
use App\MessageFile;
use App\MessageOmemoHeader;
use App\Reaction;
use App\Url;
use Moxl\Xec\Action\BOB\Request;
use Moxl\Xec\Action\Disco\Request as DiscoRequest;

use Illuminate\Database\Capsule\Manager as DB;

use Movim\ChatStates;
use Movim\ChatOwnState;
use Movim\EmbedLight;
use Movim\Image;

class Chat extends \Movim\Widget\Base
{
    private $_pagination = 50;
    private $_wrapper = [];
    private $_messageTypes = ['chat', 'headline', 'invitation', 'jingle_incoming', 'jingle_outgoing', 'jingle_end'];
    private $_mucPresences = [];

    public function load()
    {
        $this->addjs('chat.js');

        $this->addcss('chat.css');
        $this->registerEvent('carbons', 'onMessage');
        $this->registerEvent('message', 'onMessage');
        $this->registerEvent('presence', 'onPresence', 'chat');
        $this->registerEvent('retracted', 'onRetracted');
        $this->registerEvent('receiptack', 'onMessageReceipt');
        $this->registerEvent('displayed', 'onMessage', 'chat');
        $this->registerEvent('mam_get_handle', 'onMAMRetrieved');
        $this->registerEvent('mam_get_handle_muc', 'onMAMMucRetrieved', 'chat');
        $this->registerEvent('mam_get_handle_contact', 'onMAMContactRetrieved', 'chat');
        $this->registerEvent('chatstate', 'onChatState', 'chat');
        //$this->registerEvent('subject', 'onConferenceSubject', 'chat'); Spam the UI during authentication
        $this->registerEvent('muc_setsubject_handle', 'onConferenceSubject', 'chat');
        $this->registerEvent('muc_getconfig_handle', 'onRoomConfig', 'chat');
        $this->registerEvent('muc_setconfig_handle', 'onRoomConfigSaved', 'chat');
        $this->registerEvent('muc_setconfig_error', 'onRoomConfigError', 'chat');
        $this->registerEvent('presence_muc_handle', 'onMucConnected', 'chat');
        $this->registerEvent('message_publish_error', 'onPublishError', 'chat');

        $this->registerEvent('chat_counter', 'onCounter', 'chat');

        $this->registerEvent('jingle_message', 'onJingleMessage');

        $this->registerEvent('bob_request_handle', 'onSticker');
        $this->registerEvent('notification_counter_clear', 'onNotificationCounterClear');
    }

    public function onPresence($packet)
    {
        if ($packet->content && $jid = $packet->content->jid) {
            $arr = explode('|', (new Notification)->getCurrent());

            if (isset($arr[1]) && $jid == $arr[1] && !$packet->content->muc) {
                $this->ajaxGetHeader($jid);
            }
        }
    }

    public function onJingleMessage($packet)
    {
        $this->onMessage($packet, false, false);
    }

    public function onMessageReceipt($packet)
    {
        $this->onMessage($packet, false, true);
    }

    public function onRetracted($packet)
    {
        $this->onMessage($packet, false, true);
    }

    public function onCounter($count)
    {
        $this->rpc('MovimTpl.fill', '#chatheadercounter', $this->prepareChatCounter($count));
    }

    private function prepareChatCounter(int $count = 0)
    {
        $view = $this->tpl();
        $view->assign('count', $count);
        return $view->draw('_chat_counter');
    }

    public function onNotificationCounterClear($params)
    {
        list($page, $jid) = array_pad($params, 3, null);

        if ($page === 'chat') {
            // Check if the jid is a connected chatroom
            $presence = $this->user->session->presences()
                ->where('jid', $jid)
                ->where('mucjid', $this->user->id)
                ->first();

            $this->prepareMessages($jid, ($presence), true);
        }
    }

    public function onPublishError($packet)
    {
        Toast::send(
            $packet->content ??
            $this->__('chat.publish_error')
        );
    }

    public function onMessage($packet, $history = false, $receipt = false)
    {
        $message = $packet->content;
        $from = null;
        $chatStates = ChatStates::getInstance();

        $rawbody = $message->body;

        if ($message->isEmpty() && !in_array($message->type, ['jingle_incoming', 'jingle_outgoing', 'jingle_end'])) {
            return;
        }

        if ($message->file) {
            $rawbody = '📄 ' . $this->__('avatar.file');

            if (typeIsPicture($message->file['type'])) {
                $rawbody = '🖼️ ' . $this->__('chats.picture');
            }
            if (typeIsVideo($message->file['type'])) {
                $rawbody = '🎞️ ' . $this->__('chats.video');
            }
        }

        if ($message->user_id == $message->jidto
        && !$history
        && !$message->isEmpty()
        && $message->seen == false
        && $message->jidfrom != $message->jidto) {
            $from = $message->jidfrom;
            $contact = App\Contact::firstOrNew(['id' => $from]);

            $conference = $message->type == 'groupchat'
                ? $this->user->session
                    ->conferences()->where('conference', $from)
                    ->first()
                : null;

            if ($contact != null
            && $message->type != 'groupchat'
            && !$message->retracted
            && !$message->oldid) {
                $roster = $this->user->session->contacts()->where('jid', $from)->first();
                $chatStates->clearState($from);

                $name = $roster ? $roster->truename : $contact->truename;

                // Specific case where the message is a MUC PM
                $jid = explodeJid($message->jidfrom);
                if ($jid['username'] == $name && $jid['resource'] == $message->resource) {
                    $name = $message->resource;
                }

                Notification::rpcCall('Notification.incomingMessage');
                Notification::append(
                    'chat|'.$from,
                    $name,
                    $message->encrypted && is_array($message->omemoheader)
                        ? "🔒 " . substr($message->omemoheader['payload'], 0, strlen($message->omemoheader['payload'])/2)
                        : $rawbody,
                    $contact->getPhoto(),
                    4,
                    $this->route('chat', $contact->jid)
                );
            }
            // If it's a groupchat message
            elseif ($message->type == 'groupchat'
                && !$message->retracted
                && $conference
                && (($conference->notify == 1 && $message->quoted) // When quoted
                  || $conference->notify == 2) // Always
                && !$receipt) {
                Notification::rpcCall('Notification.incomingMessage');
                Notification::append(
                    'chat|'.$from,
                    ($conference != null && $conference->name)
                        ? $conference->name
                        : $from,
                    $message->resource.': '.$rawbody,
                    $conference->getPhoto(),
                    4,
                    $this->route('chat', [$contact->jid, 'room'])
                );
            } elseif ($message->type == 'groupchat') {
                if ($conference && $conference->notify == 0) {
                    $message->seen = true;
                    $message->save();
                }

                $chatStates->clearState($from, $message->resource);
            }

            $this->onChatState($chatStates->getState($from));
        }

        $this->rpc('Chat.appendMessagesWrapper', $this->prepareMessage($message, $from));
        $this->event('chat_counter', $this->user->unreads());
    }

    public function onSticker($packet)
    {
        list($to, $cid) = array_values($packet->content);
        $this->ajaxGet($to);
    }

    public function onChatState(array $array, $first = true)
    {
        if (isset($array[1])) {
            $this->setState(
                $array[0],
                is_array($array[1]) && !empty($array[1])
                    ? $this->prepareComposeList(array_keys($array[1]))
                    : $this->__('message.composing'),
                $first
            );
        } else {
            $this->setState($array[0], '', $first);
        }
    }

    public function onConferenceSubject($packet)
    {
        $this->ajaxGetRoom($packet->content->jidfrom, false, true);
    }

    public function onMAMRetrieved()
    {
        Toast::send($this->__('chat.mam_retrieval'));
    }

    public function onMAMMucRetrieved($packet)
    {
        $this->ajaxGetRoom($packet->content, true, true);
    }

    public function onMAMContactRetrieved($packet)
    {
        $this->ajaxGet($packet->content, true);
    }

    public function onMucConnected($packet)
    {
        list($content, $notify) = $packet->content;

        if ($notify) {
            $this->ajaxGetRoom($content->jid, false, true);
        }
    }

    public function onRoomConfigError($packet)
    {
        Toast::send($packet->content);
    }

    public function onRoomConfig($packet)
    {
        list($config, $room) = array_values($packet->content);

        $view = $this->tpl();

        $xml = new \XMPPtoForm;
        $form = $xml->getHTML($config->x);

        $view->assign('form', $form);
        $view->assign('room', $room);

        Dialog::fill($view->draw('_chat_config_room'), true);
    }

    public function onRoomConfigSaved($packet)
    {
        $r = new DiscoRequest;
        $r->setTo($packet->content)
          ->request();

        Toast::send($this->__('chatroom.config_saved'));
    }

    private function setState(string $jid, string $message, $first = true)
    {
        if ($first) {
            $this->rpc('MovimUtils.removeClass', '#' . cleanupId($jid.'_state'), 'first');
        }
        $this->rpc('MovimTpl.fill', '#' . cleanupId($jid.'_state'), $message);
    }

    public function ajaxInit()
    {
        $view = $this->tpl();
        $date = $view->draw('_chat_date');
        $separator = $view->draw('_chat_separator');

        $this->rpc('Chat.setGeneralElements', $date, $separator);
        $this->rpc('Chat.setConfig',
            $this->_pagination,
            $this->__('message.error'),
            $this->__('chat.action_impossible_encrypted')
        );
    }

    public function ajaxClearCounter(string $jid)
    {
        $this->prepareMessages($jid, false, true, false);
    }

    /**
     * Get the header
     */
    public function ajaxGetHeader(string $jid, bool $muc = false)
    {
        $this->rpc(
            'MovimTpl.fill',
            '#' . cleanupId($jid.'_header'),
            $this->prepareHeader($jid, $muc)
        );

        $chatStates = ChatStates::getInstance();
        $this->onChatState($chatStates->getState($jid), false);
    }

    public function ajaxHttpGetEmpty()
    {
        $this->ajaxGet();
    }

    /**
     * @brief Get a discussion
     * @param string $jid
     */
    public function ajaxGet(string $jid = null, ?bool $light = false)
    {
        if ($jid == null) {
            $this->rpc('MovimTpl.hidePanel');
            $this->rpc('Notification.current', 'chat');
            $this->rpc('MovimUtils.pushState', $this->route('chat'));
            if ($light == false) {
                $this->rpc('MovimTpl.fill', '#chat_widget', $this->prepareEmpty());
            }
        } else {
            if ($light == false) {
                $this->rpc('MovimUtils.pushState', $this->route('chat', $jid));
                $this->rpc('MovimTpl.fill', '#chat_widget', $this->prepareChat($jid));

                $chatStates = ChatStates::getInstance();
                $this->onChatState($chatStates->getState($jid), false);

                $this->rpc('MovimTpl.showPanel');
                $this->rpc('Chat.focus');
            }

            $this->rpc('Chat.setObservers');
            $this->prepareMessages($jid);
            $this->rpc('Notification.current', 'chat|'.$jid);
            $this->rpc('Chat.scrollToSeparator');

            // OMEMO
            $this->rpc(
                'Chat.setBundlesIds',
                $jid,
                $this->user->bundles()
                     ->where('jid', $jid)
                     ->select(['bundleid', 'jid'])
                     ->get()
                     ->mapToGroups(function ($tuple) {
                        return [$tuple['jid'] => $tuple['bundleid']];
                    })
                    ->toArray()
            );
        }
    }

    /**
     * @brief Get a chatroom
     * @param string $jid
     */
    public function ajaxGetRoom(string $room, $light = false, $noConnect = false)
    {
        if (!validateJid($room)) {
            return;
        }

        $conference = $this->user->session->conferences()->where('conference', $room)->with('members')->first();

        if ($conference) {
            if (!$conference->connected && !$noConnect) {
                $this->rpc('Rooms_ajaxJoin', $conference->conference, $conference->nick);
            }

            if ($light == false) {
                $this->rpc('MovimUtils.pushState', $this->route('chat', [$room, 'room']));
                $this->rpc('MovimTpl.fill', '#chat_widget', $this->prepareChat($room, true));

                $chatStates = ChatStates::getInstance();
                $this->onChatState($chatStates->getState($room), false);

                $this->rpc('MovimTpl.showPanel');
                $this->rpc('Chat.focus');
            }

            $this->rpc('Chat.setObservers');
            $this->prepareMessages($room, true);
            $this->rpc('Notification.current', 'chat|'.$room);
            $this->rpc('Chat.scrollToSeparator');

            // OMEMO
            if ($conference->isGroupChat()) {
                $this->rpc('Chat.setGroupChatMembers', $conference->members->pluck('jid')->toArray());
                $this->rpc(
                    'Chat.setBundlesIds',
                    $room,
                    $this->user->bundles()
                         ->whereIn('jid', function ($query) use ($room) {
                            $query->select('jid')
                                ->from('members')
                                ->where('conference', $room);
                         })
                         ->select(['bundleid', 'jid'])
                         ->get()
                         ->mapToGroups(function ($tuple) {
                            return [$tuple['jid'] => $tuple['bundleid']];
                        })
                        ->toArray()
                );
            }
        } else {
            $this->rpc('RoomsUtils_ajaxAdd', $room);
        }
    }

    /**
     * @brief Send a message
     */
    public function ajaxHttpDaemonSendMessage(
        string $to,
        string $message,
        bool $muc = false,
        $file = null,
        ?int $replyToMid = 0,
        ?bool $mucReceipts = false,
        $omemo = null
    ) {
        $messageFile = null;
        $messageOMEMOHeader = null;

        if ($file) {
            $messageFile = new MessageFile;
            $messageFile->import($file);

            if (!$messageFile->valid) $messageFile = null;
        } else {
            try {
                $url = new Url;
                $cache = $url->resolve(trim($message));

                if ($cache && $url->file !== null) {
                    $messageFile = $url->file;
                }
            } catch (\Exception $e) {}
        }

        if ($omemo) {
            $messageOMEMOHeader = new MessageOMEMOHeader;
            $messageOMEMOHeader->import($omemo);
        }

        $this->sendMessage($to, $message, $muc, null, $messageFile, $replyToMid, $mucReceipts, $messageOMEMOHeader);
    }

    /**
     * @brief Send a resolved message
     *
     * @param string $to
     * @param string $message
     * @return void
     */
    public function sendMessage(string $to, string $message = '', bool $muc = false,
        ?Message $replace = null, ?MessageFile $file = null, ?int $replyToMid = 0,
        ?bool $mucReceipts = false, ?MessageOMEMOHeader $messageOMEMOHeader = null)
    {
        $tempId = null;

        if ($messageOMEMOHeader) {
            $tempId = $message;
            $message = 'Encrypted OMEMO message sent';
        }

        $body = ($file != null && $file->type != 'xmpp/uri')
            ? $file->uri
            : $message;

        if ($body == '' || $body == '/me') {
            return;
        }

        $m = new \App\Message;
        $m->id          = generateUUID();
        $m->thread      = generateUUID();
        $m->originid    = $m->id;
        $m->replaceid   = $replace ? $replace->originid : null;
        $m->user_id     = $this->user->id;
        $m->jidto       = echapJid($to);
        $m->jidfrom     = $this->user->id;
        $m->published   = gmdate('Y-m-d H:i:s');

        $reply = null;

        if ($replyToMid !== 0) {
            $reply = $this->user->messages()
                          ->where('mid', $replyToMid)
                          ->first();

            if ($reply) {
                // See https://xmpp.org/extensions/xep-0201.html#new
                $m->thread = $reply->thread;
                $m->parentmid = $reply->mid;
            }
        }

        $m->markable = true;
        $m->seen = true;
        $m->type    = 'chat';
        $m->resource = $this->user->session->resource;

        if ($muc) {
            $m->type        = 'groupchat';
            $m->resource    = $this->user->session->username;
            $m->jidfrom     = $to;
        }

        // We decode URL codes to send the correct message to the XMPP server
        $p = new Publish;
        $p->setTo($to);
        $p->setReplace($m->replaceid);
        $p->setId($m->id);
        $p->setThreadid($m->thread);
        $p->setOriginid($m->originid);

        if ($muc) {
            $p->setMuc();

            if ($mucReceipts) {
                $p->setMucReceipts();
            }
        }

        if ($file) {
            $m->file = (array)$file;
            $p->setFile($file);
        }

        if ($reply) {
            $quotable = false;

            // https://xmpp.org/extensions/xep-0461.html#business-id
            if ($reply->type == 'groupchat' && substr($reply->id, 0, 2) != 'm_') {
                // stanza-id only
                $p->setReplyid($reply->id);
                $quotable = true;
            } elseif ($reply->type != 'groupchat' && $reply->originid) {
                $p->setReplyid($reply->originid);
                $quotable = true;
            } elseif ($reply->type != 'groupchat' && substr($reply->id, 0, 2) != 'm_') {
                $p->setReplyid($reply->id);
                $quotable = true;
            }

            if ($quotable) {
                $p->setReplyto($reply->jidfrom.'/'.$reply->resource);

                $p->setReplyquotedbodylength(
                    mb_strlen(htmlspecialchars($reply->body, ENT_NOQUOTES)) + 2 // 2 = > quote character
                );

                // Prepend quoted message body
                $quotedBody = preg_replace('/^/m', '> ', $reply->body) . "\n";
                $p->setContent($quotedBody . $body);
            } else {
                $p->setContent($body);
            }
        } else {
            $p->setContent($body);
        }

        $m->body = $body;

        if ($messageOMEMOHeader) {
            $m->encrypted = true;
            $m->omemoheader = (string)$messageOMEMOHeader;
            $m->bundleid = $messageOMEMOHeader->sid;
            $p->setMessageOMEMO($messageOMEMOHeader);
        }

        (ChatOwnState::getInstance())->halt();

        $p->request();

        // We sent the published id back
        if ($tempId) {
            $this->rpc('Chat.sentId', $tempId, $m->id);
        }

        /* Is it really clean ? */
        if (!$p->getMuc()) {
            $m->body = htmlentities(trim($m->body), ENT_XML1, 'UTF-8');
            $m->save();

            $m = $m->fresh();

            $packet = new \Moxl\Xec\Payload\Packet;
            $packet->content = $m;

            // We refresh the Chats list
            $c = new Chats;
            $c->onMessage($packet);

            $this->onMessage($packet);
        }
    }

    /**
     * @brief Send a correction message
     *
     * @param string $to
     * @param string $message
     * @return void
     */
    public function ajaxHttpDaemonCorrect(string $to, int $mid, string $message = '')
    {
        $replace = $this->user->messages()
                        ->where('mid', $mid)
                        ->first();

        if ($replace) {
            $this->sendMessage($to, $message, $replace->isMuc(), $replace);
        }
    }

    /**
     * @brief Send a reaction
     */
    public function ajaxHttpDaemonSendReaction(string $mid, string $emoji)
    {
        $parentMessage = $this->user->messages()
                        ->where('mid', $mid)
                        ->first();

        $emojiHandler = \Movim\Emoji::getInstance();
        $emojiHandler->replace($emoji);

        if ($parentMessage && $emojiHandler->isSingleEmoji()) {
            // Try to load the MUC presence and resolve the resource
            $mucPresence = null;
            if ($parentMessage->type == 'groupchat') {
                $mucPresence = $this->user->session->presences()
                                    ->where('jid', $parentMessage->jidfrom)
                                    ->where('mucjid', $this->user->id)
                                    ->where('muc', true)
                                    ->first();

                if (!$mucPresence) return;
            }

            $jidfrom = ($parentMessage->type == 'groupchat')
                ? $mucPresence->resource
                : $this->user->id;

            $emojis = $parentMessage->reactions()
                ->where('jidfrom', $jidfrom)
                ->get();

            $r = new Reactions;
            $newEmojis = [];

            // This reaction was not published yet
            if ($emojis->where('emoji', $emoji)->count() == 0) {
                $reaction = new Reaction;
                $reaction->message_mid = $parentMessage->mid;
                $reaction->jidfrom = ($parentMessage->type == 'groupchat')
                    ? $this->user->session->username
                    : $this->user->id;
                $reaction->emoji = $emoji;

                if ($parentMessage->type != 'groupchat') {
                    $reaction->save();
                }

                $newEmojis = $emojis->push($reaction);
            } else {
                if ($parentMessage->type != 'groupchat') {
                    $parentMessage->reactions()
                        ->where('jidfrom', $jidfrom)
                        ->where('emoji', $emoji)
                        ->delete();
                }

                $newEmojis = $emojis->filter(function ($value, $key) use ($emoji) {
                    return $value->emoji != $emoji;
                });
            }

            $r->setTo($parentMessage->jidfrom != $parentMessage->user_id
                ? $parentMessage->jidfrom
                : $parentMessage->jidto)
              ->setId(\generateUUID())
              ->setParentId(!$parentMessage->isMuc() && $parentMessage->originid
                ? $parentMessage->originid
                : $parentMessage->id)
              ->setReactions($newEmojis->pluck('emoji')->toArray());

            if ($parentMessage->type == 'groupchat') {
                $r->setMuc();
            }

            $r->request();

            if ($parentMessage->type != 'groupchat') {
                $packet = new \Moxl\Xec\Payload\Packet;
                $packet->content = $parentMessage;
                $this->onMessage($packet);
            }
        }
    }

    /**
     * @brief Refresh a message
     */
    public function ajaxRefreshMessage(string $mid)
    {
        $message = $this->user->messages()
                              ->where('mid', $mid)
                              ->first();

        if ($message) {
            $this->rpc('Chat.appendMessagesWrapper', $this->prepareMessage($message, null));
        }
    }

    /**
     * @brief Get the last message sent
     *
     * @param string $to
     * @return void
     */
    public function ajaxLast($to, $muc = false)
    {
        if ($muc) {
            // Resolve the current presence
            $presence = $this->user->session->presences()
            ->where('jid', $to)
            ->where('muc', true)
            ->where('mucjid', $this->user->id)
            ->first();

            if ($presence) {
                $m = $this->user->messages()
                          ->where('type', 'groupchat')
                          ->where('jidfrom', $to)
                          ->where('jidto', $this->user->id)
                          ->where('resource', $presence->resource)
                          ->orderBy('published', 'desc')
                          ->first();
            }
        } else {
            $m = $this->user->messages()
                            ->where('jidto', $to)
                            ->orderBy('published', 'desc')
                            ->first();
        }

        if (!$m) return;

        // We might get an already edited message, be sure to load the id of the original one
        $mid = $m->mid;

        if ($m && !empty($m->replaceid)) {
            $originalMessage = $this->user->messages()
                                        ->where('originid', $m->replaceid)
                                        ->first();

            if ($originalMessage) {
                $mid = $originalMessage->mid;
            }
        }

        if ($m
        && !isset($m->sticker)
        && !isset($m->file)
        && !empty($m->body)) {
            $this->rpc('Chat.setTextarea', htmlspecialchars_decode($m->body), $mid);
        }
    }

    /**
     * @brief Get a sent message
     *
     * @param string $mid
     * @return void
     */
    public function ajaxEdit($mid)
    {
        $m = $this->user->messages()
                        ->where('mid', $mid)
                        ->first();

        if ($m
        && !isset($m->sticker)
        && !isset($m->file)) {
            $this->rpc('Chat.setTextarea', htmlspecialchars_decode($m->body), $mid);
        }
    }

    /**
     * @brief Reply to a message
     *
     * @param string $mid
     * @return void
     */
    public function ajaxHttpDaemonReply($mid)
    {
        $m = $this->user->messages()
                        ->where('mid', $mid)
                        ->first();

        if (($m->id && substr($m->id, 0, 2) != 'm_') || isset($m->thread)) {
            $view = $this->tpl();
            $view->assign('message', $m);
            $this->rpc('MovimTpl.fill', '#reply', $view->draw('_chat_reply'));
            $this->rpc('Chat.focus');
        }
    }

    /**
     * Clear the Reply box
     */
    public function ajaxClearReply()
    {
        $this->rpc('MovimTpl.fill', '#reply', '');
    }

    /**
     * @brief Send a "composing" message
     *
     * @param string $to
     * @return void
     */
    public function ajaxSendComposing($to, $muc = false)
    {
        if (!validateJid($to)) {
            return;
        }

        (ChatOwnState::getInstance())->composing($to, $muc);
    }

    /**
     * @brief Get the chat history
     *
     * @param string jid
     * @param string time
     */
    public function ajaxGetHistory($jid, $date, $muc = false, $prepend = true)
    {
        if (!validateJid($jid) && isset($date)) {
            return;
        }

        $messages = \App\Message::jid($jid)
            ->where('published', $prepend ? '<' : '>', date(MOVIM_SQL_DATE, strtotime($date)));

        $messages = $muc
            ? $messages->where('type', 'groupchat')->whereNull('subject')
            : $messages->whereIn('type', $this->_messageTypes);

        $messages = $messages->orderBy('published', 'desc')
                             ->withCount('reactions')
                             ->take($this->_pagination)
                             ->get();

        if ($messages->count() > 0) {
            if ($prepend) {
                Toast::send($this->__('message.history', $messages->count()));
            } else {
                $messages = $messages->reverse();
            }

            foreach ($messages as $message) {
                $this->prepareMessage($message);
            }

            $this->rpc('Chat.appendMessagesWrapper', $this->_wrapper, $prepend);
            $this->_wrapper = [];
        }
    }

    /**
     * @brief Configure a room
     *
     * @param string $room
     */
    public function ajaxGetRoomConfig($room)
    {
        if (!validateJid($room)) {
            return;
        }

        $gc = new GetConfig;
        $gc->setTo($room)
           ->request();
    }

    /**
     * @brief Save the room configuration
     *
     * @param string $room
     */
    public function ajaxSetRoomConfig($data, $room)
    {
        if (!validateJid($room)) {
            return;
        }

        $sc = new SetConfig;
        $sc->setTo($room)
           ->setData($data)
           ->request();
    }

    /**
     * @brief Set last displayed message
     */
    public function ajaxDisplayed($jid, $id)
    {
        if (!validateJid($jid)) {
            return;
        }

        $message = $this->user->messages()->where('id', $id)->first();

        if ($message
        && $message->markable == true
        && $message->displayed == null) {
            $message->displayed = gmdate('Y-m-d H:i:s');
            $message->save();

            \Moxl\Stanza\Message::displayed(
                $jid,
                $message->originid ?? $message->id,
                $message->type
            );
        }
    }

    /**
     * @brief Ask to clear the history
     *
     * @param string $jid
     */
    public function ajaxClearHistory($jid)
    {
        $view = $this->tpl();
        $view->assign('jid', $jid);
        $view->assign('count', \App\Message::jid($jid)->count());

        Dialog::fill($view->draw('_chat_clear'));
    }

    /**
     * @brief Clear the history
     *
     * @param string $jid
     */
    public function ajaxClearHistoryConfirm($jid)
    {
        if (!validateJid($jid)) {
            return;
        }

        \App\Message::whereIn('id', function ($query) use ($jid) {
            $jidFromToMessages = DB::table('messages')
                ->where('user_id', $this->user->id)
                ->where('jidfrom', $jid)
                ->unionAll(DB::table('messages')
                    ->where('user_id', $this->user->id)
                    ->where('jidto', $jid)
                );

            $query->select('id')->from(
                $jidFromToMessages,
                'messages'
            )->where('user_id', $this->user->id);
        })->delete();

        $this->ajaxGet($jid);
    }

    public function prepareChat($jid, $muc = false)
    {
        $view = $this->tpl();

        $view->assign('jid', $jid);
        $view->assign('muc', $muc);
        $view->assign('emoji', prepareString('😀'));

        if ($muc) {
            $view->assign('conference', $this->user->session->conferences()
                                             ->where('conference', $jid)
                                             ->with('info')
                                             ->first());
        }

        return $view->draw('_chat');
    }

    public function prepareMessages($jid, $muc = false, $seenOnly = false, $event = true)
    {
        if (!validateJid($jid)) {
            return;
        }

        $jid = echapJid($jid);

        $messagesQuery = \App\Message::jid($jid);

        $messagesQuery = $muc
            ? $messagesQuery->where('type', 'groupchat')->whereNull('subject')
            : $messagesQuery->whereIn('type', $this->_messageTypes);

        /**
         * The object need to be cloned there for MySQL, looks like the pagination/where is kept somewhere in between…
         **/
        $messagesRequest = clone $messagesQuery;
        $messagesCount = clone $messagesQuery;

        $messages = $messagesRequest->withCount('reactions')->orderBy('published', 'desc')->take($this->_pagination)->get();
        $unreadsCount = $messagesCount->where('seen', false)->count();

        if ($unreadsCount > 0) {
            $messagesClear = clone $messagesQuery;
            // Two queries as Eloquent doesn't seems to map correctly the parameters
            \App\Message::whereIn('mid', $messagesClear->where('seen', false)->pluck('mid'))->update(['seen' => true]);
        }

        // Prepare the muc presences if possible
        $firstMessage = $messages->first();
        if ($firstMessage && $firstMessage->type == 'groupchat') {
            $this->_mucPresences = $this->user->session->presences()
                ->where('jid', $firstMessage->jidfrom)
                ->where('muc', true)
                ->whereIn('resource', $messages->pluck('resource')->unique())
                ->get()
                ->keyBy(function($presence) {
                    return $presence->jid.$presence->resource;
                });
        }

        if (!$seenOnly) {
            $messages = $messages->reverse();

            foreach ($messages as $message) {
                $this->prepareMessage($message);
            }

            $view = $this->tpl();
            $view->assign('jid', $jid);

            $view->assign('contact', \App\Contact::firstOrNew(['id' => $jid]));
            $view->assign('me', false);
            $view->assign('muc', $muc);
            $left = $view->draw('_chat_bubble');

            $view->assign('contact', \App\Contact::firstOrNew(['id' => $this->user->id]));
            $view->assign('me', true);
            $view->assign('muc', $muc);
            $right = $view->draw('_chat_bubble');

            $this->rpc('Chat.setSpecificElements', $left, $right);
            $this->rpc('Chat.appendMessagesWrapper', $this->_wrapper, false);
        }

        if ($messages->count() == 0 && !$muc) {
            //$chats = new Chats;
            //$chats->ajaxGetHistory($jid);
        }

        if ($event) {
            $this->event($muc ? 'chat_open_room' : 'chat_open', $jid);
        }
        $this->event('chat_counter', $this->user->unreads());

        $this->rpc('Chat.insertSeparator', $unreadsCount);
    }

    public function prepareMessage(&$message, $jid = null)
    {
        if ($jid != $message->jidto && $jid != $message->jidfrom && $jid != null) {
            return $this->_wrapper;
        }

        $message->jidto = echapJS($message->jidto);
        $message->jidfrom = echapJS($message->jidfrom);

        $emoji = \Movim\Emoji::getInstance();

        // URL messages
        if (!empty($message->body)) {
            $message->url = filter_var(trim($message->body), FILTER_VALIDATE_URL);
        }

        // If the message doesn't contain a file but is a URL, we try to resolve it
        if (!$message->file && $message->url && $message->resolved == false) {
            $this->rpc('Chat.resolveMessage', (int)$message->mid);
        }

        if ($message->retracted) {
            $message->body = '<i class="material-icons">delete</i> '.__('message.retracted');
        } elseif ($message->encrypted) {
            $message->body = __('message.encrypted');
        } elseif (isset($message->html) && !isset($message->file)) {
            $message->body = $message->html;
        } else {
            $message->addUrls();
            $message->body = $emoji->replace($message->body);
        }

        if (isset($message->subject) && $message->type == 'headline') {
            $message->body = $message->subject .': '. $message->body;
        }

        // XEP-0393
        // $message->body = (preg_replace ('/```((.|\n)*?)(```|\z)/', "<pre>$1</pre>", $message->body));
        $message->body = (preg_replace ('/(?<=^|[\s,\*,_,~])(`(?!\s).+?(?<!\s)`)/', "$1</code>", $message->body));
        $message->body = (preg_replace ('/(?<=^|[\s,_,`,~])(\*(?!\s).+?(?<!\s)\*)/', "<b>$1</b>", $message->body));
        $message->body = (preg_replace ('/(?<=^|[\s,\*,`,~])(_(?!\s).+?(?<!\s)_)/', "<em>$1</em>", $message->body));
        $message->body = (preg_replace ('/(?<=^|[\s,\*,_,`])(~(?!\s).+?(?<!\s)~)/', "<s>$1</s>", $message->body));

        // Sticker message
        if (isset($message->sticker)) {
            $sticker = Image::getOrCreate($message->sticker, false, false, 'png');

            if ($sticker == false
            && $message->jidfrom != $message->session) {
                $r = new Request;
                $r->setTo($message->jidfrom)
                    ->setResource($message->resource)
                    ->setCid($message->sticker)
                    ->request();
            } else {
                $p = new Image;
                $p->setKey($message->sticker);
                $p->load('png');
                $stickerSize = $p->getGeometry();

                $message->sticker = [
                    'url' => $sticker,
                    'width' => $stickerSize['width'],
                    'height' => $stickerSize['height']
                ];
            }
        }

        // Jumbo emoji
        if ($emoji->isSingleEmoji()
            && !isset($message->html)
            && in_array($message->type,  ['chat', 'groupchat'])) {
            $message->sticker = [
                'url' => $emoji->getLastSingleEmojiURL(),
                'title' => ':'.$emoji->getLastSingleEmojiTitle().':',
                'height' => 60,
            ];
        }

        // Attached file
        if (isset($message->file)) {
            // We proxify pictures links even if they are advertized as small ones
            if (\array_key_exists('type', $message->file)
            && typeIsPicture($message->file['type'])
            && $message->file['size'] <= SMALL_PICTURE_LIMIT*4) {
                $message->sticker = [
                    'thumb' => $this->route('picture', urlencode($message->file['uri'])),
                    'url' => $message->file['uri'],
                    'picture' => true
                ];
            }

            $url = parse_url($message->file['uri']);

            // Other image websites
            if (\array_key_exists('host', $url)) {
                $file = $message->file;
                $file['host'] = $url['host'];
                $message->file = $file;

                switch ($url['host']) {
                    case 'i.imgur.com':
                        $thumb = getImgurThumbnail($message->file['uri']);

                        if ($thumb) {
                            $message->sticker = [
                                'url' => $message->file['uri'],
                                'thumb' => $thumb,
                                'picture' => true
                            ];
                        }
                        break;
                }
            }

            // Build cards for the URIs
            $uri = explodeXMPPURI($message->file['uri']);

            switch ($uri['type']) {
                case 'post':
                    $post = \App\Post::where('server', $uri['params'][0])
                        ->where('node',  $uri['params'][1])
                        ->where('nodeid',  $uri['params'][2])
                        ->first();

                    if ($post) {
                        $p = new Post;
                        $message->card = $p->prepareTicket($post);
                    }
                    break;
            }
        }

        if ($message->resolvedUrl && !$message->file
        && !$message->card && !$message->sticker) {
            $resolved = $message->resolvedUrl->cache;
            if ($resolved) {
                $message->card =  $this->prepareEmbed($resolved);
            }
        }

        // Parent
        if ($message->parent) {
            if ($message->parent->file) {
                $message->parent->body = '<i class="material-icons">insert_drive_file</i> '.__('avatar.file');

                if (typeIsPicture($message->parent->file['type'])) {
                    $message->parent->body = '<i class="material-icons">image</i> '.__('chats.picture');
                }
                if (typeIsVideo($message->parent->file['type'])) {
                    $message->parent->body = '<i class="material-icons">local_movies</i> '.__('chats.video');
                }
            }

            if ($message->parent->type == 'groupchat') {
                $message->parent->resolveColor();
                $message->parent->fromName = $message->parent->resource;
            } else {
                // TODO optimize
                $roster = $this->user->session->contacts()
                            ->where('jid', $message->parent->jidfrom)
                            ->first();

                $contactFromName = $message->parent->from
                    ? $message->parent->from->truename
                    : $message->parent->jidfrom;

                $message->parent->fromName = $roster
                    ? $roster->truename
                    : $contactFromName;
            }
        } else {
            // Let's try to support "quoted" messages
            $quote = '&gt; ';
            $parent = '';
            $remains = '';
            $endOfQuote = false;

            foreach (explode(PHP_EOL, $message->body) as $line) {
                if (substr($line, 0, strlen($quote)) == $quote && $endOfQuote == false) {
                    $parent .= substr($line, strlen($quote))."\n";
                } else {
                    $endOfQuote = true;
                    $remains .= $line."\n";
                }
            }

            if ($parent !== '') {
                $message->parentQuote = $parent;
                $message->body = $remains;
            }
        }

        // reactions_count if cached, if not, reload it from the DB
        if ($message->reactions_count ?? $message->reactions()->count()) {
            $message->reactionsHtml = $this->prepareReactions($message);
        }

        $message->rtl = isRTL($message->body);
        $message->publishedPrepared = prepareTime(strtotime($message->published));

        if ($message->delivered) {
            $message->delivered = prepareDate(strtotime($message->delivered), true);
        }

        if ($message->displayed) {
            $message->displayed = prepareDate(strtotime($message->displayed), true);
        }

        $date = prepareDate(strtotime($message->published), false, false, true);

        if (empty($date)) {
            $date = $this->__('date.today');
        }

        // We create the date wrapper
        if (!array_key_exists($date, $this->_wrapper)) {
            $this->_wrapper[$date] = [];
        }

        $messageDBSeen = $message->seen;
        $n = new Notification;

        if ($message->type == 'groupchat') {
            $message->resolveColor();

            // Cache the resolved presences for a while
            $key = $message->jidfrom.$message->resource;
            if (!isset($this->_mucPresences[$key])) {
                $this->_mucPresences[$key] = $this->user->session->presences()
                           ->where('jid', $message->jidfrom)
                           ->where('resource', $message->resource)
                           ->where('muc', true)
                           ->first();
            }

            if ($this->_mucPresences[$key] && $this->_mucPresences[$key] !== true) {
                if ($url = $this->_mucPresences[$key]->conferencePicture) {
                    $message->icon_url = $url;
                }

                $message->moderator = ($this->_mucPresences[$key]->mucrole == 'moderator');
                $message->mucjid = $this->_mucPresences[$key]->mucjid;
                $message->mine = $message->seen = ($this->_mucPresences[$key]->mucjid == $this->user->id);

            } else {
                $this->_mucPresences[$key] = true;
            }

            $message->icon = firstLetterCapitalize($message->resource);
        }

        // Handle faulty replacing messages
        if ($message->replace
        && ($message->replace->jidfrom != $message->jidfrom
         || $message->replace->resource != $message->resource)
        ) {
            unset($message->replace);
            unset($message->replaceid);
        }

        if($message->seen === false) {
            $message->seen = ('chat|'.$message->jidfrom == $n->getCurrent());
        }

        if ($message->seen === true
        && $messageDBSeen === false) {
            $this->user->messages()
                 ->where('id', $message->id)
                 ->update(['seen' => true]);
        }

        $msgkey = '<' . $message->jidfrom;
        $msgkey .= ($message->type == 'groupchat' && $message->resource != null)
                    ? cleanupId($message->resource, true)
                    : '';
        $msgkey .= '>' . substr($message->published, 11, 5);

        $counter = count($this->_wrapper[$date]);

        $this->_wrapper[$date][$counter.$msgkey] = $message;

        if ($message->type == 'invitation') {
            $view = $this->tpl();
            $view->assign('message', $message);
            $message->body = ($message->jidfrom == $this->user->id)
                ? $view->draw('_chat_invitation_self')
                : $view->draw('_chat_invitation');
        }

        if ($message->type == 'jingle_incoming') {
            $view = $this->tpl();
            $view->assign('message', $message);
            $message->body = $view->draw('_chat_jingle_incoming');
        }

        if ($message->type == 'jingle_outgoing') {
            $view = $this->tpl();
            $view->assign('message', $message);
            $message->body = $view->draw('_chat_jingle_outgoing');
        }

        if ($message->type == 'jingle_end') {
            $view = $this->tpl();
            $view->assign('message', $message);
            $view->assign('diff', false);

            $start = Message::where('thread', $message->thread)
                ->whereIn('type', ['jingle_incoming', 'jingle_outgoing'])
                ->first();

            if ($start) {
                $diff = (new DateTime($start->created_at))
                  ->diff(new DateTime($message->created_at));

                $view->assign('diff', $diff);
            }

            $message->body = $view->draw('_chat_jingle_end');
        }

        return $this->_wrapper;
    }

    public function prepareEmbed(EmbedLight $embed, bool $withLink = false)
    {
        $tpl = $this->tpl();
        $tpl->assign('embed', $embed);
        $tpl->assign('withlink', $withLink);
        return $tpl->draw('_chat_embed');
    }

    public function prepareReactions(Message $message)
    {
        $view = $this->tpl();
        $merged = [];

        $reactions = $message
            ->reactions()
            ->orderBy('created_at')
            ->get();

        foreach ($reactions as $reaction) {
            if (!array_key_exists($reaction->emoji, $merged)) {
                $merged[$reaction->emoji] = [];
            }

            $merged[$reaction->emoji][] = $reaction->jidfrom;
        }

        $view->assign('message', $message);
        $view->assign('reactions', $merged);
        $view->assign('me', $this->user->id);

        return $view->draw('_chat_reactions');
    }

    public function prepareHeader($jid, $muc = false)
    {
        $view = $this->tpl();

        $view->assign('jid', $jid);
        $view->assign('muc', $muc);
        $view->assign(
            'info',
            \App\Info::where('server', $this->user->session->host)
                     ->where('node', '')
                     ->first()
        );
        $view->assign('anon', false);
        $view->assign('counter',
            $this->prepareChatCounter(
                $this->user->unreads(null, false, true)
            )
        );

        if ($muc) {
            $view->assign('conference', $this->user->session->conferences()
                                             ->where('conference', $jid)
                                             ->with('info')
                                             ->first());

            $mucinfo = \App\Info::where('server', explodeJid($jid)['server'])
                                ->where('node', '')
                                ->first();
            if ($mucinfo && !empty($mucinfo->abuseaddresses)) {
                $view->assign('info', $mucinfo);
            }
        } else {
            $view->assign('roster', $this->user->session->contacts()->where('jid', $jid)->first());
            $view->assign('contact', \App\Contact::firstOrNew(['id' => $jid]));
        }

        return $view->draw('_chat_header');
    }

    public function prepareEmpty()
    {
        $view = $this->tpl();

        $chats = \App\Cache::c('chats');
        if ($chats == null) {
            $chats = [];
        }
        $chats[$this->user->id] = true;

        $top = $this->user->session->topContacts()
            ->join(DB::raw('(
                select min(value) as value, jid as pjid
                from presences
                group by jid) as presences
            '), 'presences.pjid', '=', 'rosters.jid')
            ->where('value', '<', 5)
            ->whereNotIn('rosters.jid', array_keys($chats))
            ->with('presence.capability')
            ->take(15)
            ->get();
        $view->assign('top', $top);

        return $view->draw('_chat_empty');
    }

    public function ajaxHttpGetExplore($page = 0)
    {
        $this->rpc('MovimTpl.fill', '#chat_explore', $this->prepareExplore($page));
    }

    public function prepareExplore($page = 0)
    {
        $view = $this->tpl();

        $pagination = 8;

        $users = Contact::public()
            ->notInRoster($this->user->session->id)
            ->orderByPresence()
            ->where('id', '!=', $this->user->id)
            ->skip($page * $pagination)
            ->take($pagination + 1)
            ->get();

        $view->assign('presencestxt', getPresencesTxt());
        $view->assign('users', $users);
        $view->assign('pagination', $pagination);
        $view->assign('page', $page);

        return $view->draw('_chat_explore');
    }

    private function prepareComposeList(array $list)
    {
        $view = $this->tpl();
        $view->assign('list', implode(', ', $list));
        return $view->draw('_chat_compose_list');
    }

    public function getSmileyPath($id)
    {
        return getSmileyPath($id);
    }
}
