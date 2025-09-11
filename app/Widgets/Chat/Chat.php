<?php

namespace App\Widgets\Chat;

use Moxl\Xec\Action\Message\Publish;
use Moxl\Xec\Action\Message\Reactions;

use Moxl\Xec\Action\Muc\GetConfig;
use Moxl\Xec\Action\Muc\SetConfig;

use App\Contact;
use App\MAMEarliest;
use App\Message;
use App\MessageFile;
use App\MessageOmemoHeader;
use App\Post as AppPost;
use App\Reaction;
use App\Url;
use App\Widgets\Chats\Chats;
use App\Widgets\Dialog\Dialog;
use App\Widgets\Dictaphone\Dictaphone;
use App\Widgets\Notif\Notif;
use App\Widgets\Post\Post;
use App\Widgets\Toast\Toast;
use Carbon\Carbon;
use Moxl\Xec\Action\BOB\Request;
use Moxl\Xec\Action\Disco\Request as DiscoRequest;

use Illuminate\Database\Capsule\Manager as DB;

use Movim\ChatStates;
use Movim\ChatOwnState;
use Movim\CurrentCall;
use Movim\EmbedLight;
use Movim\Image;
use Movim\XMPPUri;
use Movim\Librairies\XMPPtoForm;

class Chat extends \Movim\Widget\Base
{
    private $_pagination = 50;
    private $_wrapper = [];
    private $_mucPresences = [];

    public function load()
    {
        $this->addjs('chat.js');

        $this->addcss('chat.css');
        $this->registerEvent('carbons', 'onMessage');
        $this->registerEvent('message', 'onMessage');
        $this->registerEvent('presence', 'onPresence', 'chat');
        $this->registerEvent('retracted', 'onRetracted');
        $this->registerEvent('moderated', 'onRetracted');
        $this->registerEvent('receiptack', 'onMessageReceipt');
        $this->registerEvent('pubsub_getitem_messageresolved', 'onPostResolved');
        $this->registerEvent('displayed', 'onMessage', 'chat');
        $this->registerEvent('mam_get_handle', 'onMAMRetrieved', 'chat');
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
        $this->registerEvent('muji_message', 'onMujiMessage');
        $this->registerEvent('muc_event_message', 'onMucEventMessage');

        $this->registerEvent('bob_request_handle', 'onSticker');
        $this->registerEvent('notification_counter_clear', 'onNotificationCounterClear');

        $this->registerEvent('currentcall_started', 'onCallEvent', 'chat');
        $this->registerEvent('currentcall_stopped', 'onCallEvent', 'chat');

        $this->registerEvent('callinvitepropose', 'onCallInvite');
        $this->registerEvent('callinviteaccept', 'onCallInvite');
        $this->registerEvent('callinviteleft', 'onCallInvite');
        $this->registerEvent('callinviteretract', 'onCallInvite');
        $this->registerEvent('presence_muji_event', 'onCallInvite');
    }

    public function onPresence($packet)
    {
        if ($packet->content && $jid = $packet->content->jid) {
            $arr = explode('|', (new Notif)->getCurrent());

            if (isset($arr[1]) && $jid == $arr[1] && !$packet->content->muc) {
                $this->ajaxGetHeader($jid);
            }
        }
    }

    public function onCallInvite($packet)
    {
        $muji = $packet->content;

        if ($muji->jidfrom && $muji->conference) {
            $this->ajaxGetHeader($muji->jidfrom, $muji->isfromconference);
        }
    }

    public function onCallEvent($packet)
    {
        $this->ajaxGetHeader($packet[0]);
    }

    public function onJingleMessage($packet)
    {
        $this->onMessage($packet);
    }

    public function onMujiMessage($packet)
    {
        $this->onMessage($packet);
    }

    public function onMucEventMessage($packet)
    {
        $this->onMessage($packet);
    }

    public function onMessageReceipt($packet)
    {
        $this->onMessage($packet, history: false, receipt: true);
    }

    public function onRetracted($packet)
    {
        $this->onMessage($packet, history: false, receipt: true);
    }

    public function onPostResolved($packet)
    {
        $this->onMessage($packet);
    }

    public function onCounter($count)
    {
        $this->rpc('MovimUtils.setDataItem', '#chatheadercounter', 'counter', $count);
    }

    public function onNotificationCounterClear($params)
    {
        $jid = $params[1] ?? null;

        if ($params[0] === 'chat' && $jid) {
            // Check if the jid is a connected chatroom
            $presence = $this->user->session->presences()
                ->where('jid', $jid)
                ->where('mucjid', $this->user->id)
                ->first();

            $this->getMessages($jid, muc: ($presence), seenOnly: true);
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

        $rawbody = $message->getInlinedBodyAttribute(true) ?? $message->body;

        if (
            $message->isEmpty() && !in_array(
                $message->type,
                array_merge(Message::MESSAGE_TYPE, Message::MESSAGE_TYPE_MUC)
            )
        ) {
            return;
        }

        if ($message->file) {
            $rawbody = '📄 ' . $this->__('avatar.file');

            if ($message->file->isPicture) {
                $rawbody = '🖼️ ' . $this->__('chats.picture');
            } elseif ($message->file->isAudio) {
                $rawbody = '🎵 ' . $this->__('chats.audio');
            } elseif ($message->file->isVideo) {
                $rawbody = '🎞️ ' . $this->__('chats.video');
            }
        }

        if (
            $message->user_id == $message->jidto
            && !$history
            && !$message->isEmpty()
            && $message->seen == false
            && $message->jidfrom != $message->jidto
        ) {
            $from = $message->jidfrom;
            $contact = Contact::firstOrNew(['id' => $from]);

            $conference = $message->isMuc()
                ? $this->user->session
                ->conferences()->where('conference', $from)
                ->first()
                : null;

            if (
                $contact != null
                && !$message->isMuc()
                && !$message->retracted
                && !$message->oldid
            ) {
                $roster = $this->user->session->contacts()->where('jid', $from)->first();
                $chatStates->clearState($from);

                $name = $roster ? $roster->truename : $contact->truename;

                // Specific case where the message is a MUC PM
                $jid = explodeJid($message->jidfrom);
                if ($jid['username'] == $name && $jid['resource'] == $message->resource) {
                    $name = $message->resource;
                }

                Notif::rpcCall('Notif.incomingMessage');

                // Prevent some spammy notifications
                if ($roster || $contact->exists) {
                    Notif::append(
                        'chat|' . $from,
                        $name,
                        $message->encrypted && is_array($message->omemoheader)
                            ? "🔒 " . substr($message->omemoheader['payload'], 0, strlen($message->omemoheader['payload']) / 2)
                            : $rawbody,
                        $contact->getPicture(),
                        6,
                        $this->route('chat', $contact->jid),
                        null,
                        'Search.chat(\'' . echapJS($contact->jid) . '\', ' . ($message->isMuc() ? 'true' : 'false') . ')'
                    );
                }
            }
            // If it's a groupchat message
            elseif (
                $message->isMuc()
                && !$message->retracted
                && $conference
                && (($conference->notify == 1 && $message->quoted) // When quoted
                    || $conference->notify == 2) // Always
                && !$receipt
            ) {
                Notif::rpcCall('Notif.incomingMessage');
                Notif::append(
                    'chat|' . $from,
                    ($conference != null && $conference->name)
                        ? $conference->name
                        : $from,
                    $message->resource . ': ' . $rawbody,
                    $conference->getPicture(),
                    4,
                    $this->route('chat', [$contact->jid, 'room'])
                );
            } elseif ($message->isMuc()) {
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

    public function onMAMRetrieved($packet)
    {
        $content = $packet->content;

        $this->rpc('MovimUtils.removeClass', '#chat_widget .contained', 'loading');

        if ($content['counter'] > 0) {
            if ($content['forward']) {
                $this->rpc('Chat.getNewerMessages');
            } else {
                $this->rpc('Chat.getHistory', false);
            }
        }
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

        $xml = new XMPPtoForm;
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
            $this->rpc('MovimUtils.removeClass', '#' . cleanupId($jid . '_state'), 'first');
        }
        $this->rpc('MovimTpl.fill', '#' . cleanupId($jid . '_state'), $message);
    }

    public function ajaxInit()
    {
        $view = $this->tpl();

        $this->rpc(
            'Chat.init',
            $view->draw('_chat_date'),
            $view->draw('_chat_separator'),
            [
                'pagination' => $this->_pagination,
                'delivery_error' => $this->__('message.error'),
                'action_impossible_encrypted_error' => $this->__('chat.action_impossible_encrypted')
            ]
        );
    }

    /**
     * Get the header
     */
    public function ajaxGetHeader(string $jid, bool $muc = false)
    {
        $this->rpc(
            'MovimTpl.fill',
            '#' . cleanupId($jid . '_header'),
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
    public function ajaxGet(?string $jid = null, ?bool $light = false)
    {
        if ($jid == null) {
            $this->rpc('MovimTpl.hidePanel');
            $this->rpc('Notif.current', 'chat');
            $this->rpc('MovimUtils.pushSoftState', $this->route('chat'));
            if ($light == false) {
                $this->rpc('MovimTpl.fill', '#chat_widget', $this->prepareEmpty());
            }
        } else {
            if ($light == false) {
                $this->rpc('MovimUtils.pushSoftState', $this->route('chat', $jid));
                $this->rpc('MovimTpl.fill', '#chat_widget', $this->prepareChat($jid));

                $chatStates = ChatStates::getInstance();
                $this->onChatState($chatStates->getState($jid), false);

                $this->rpc('MovimTpl.showPanel');
                $this->rpc('Chat.focus');

                (new Dictaphone)->ajaxHttpGet();
            }

            if (CurrentCall::getInstance()->isStarted()) {
                $this->rpc('MovimVisio.moveToChat', CurrentCall::getInstance()->getBareJid());
            }

            $this->rpc('Chat.setObservers');
            $this->rpc('MovimTpl.fill', '#' . cleanupId($jid) . '-conversation', '');
            $this->getMessages($jid);
            $this->rpc('Notif.current', 'chat|' . $jid);
            $this->rpc('Chat.scrollToSeparator');

            if ($this->user->hasOMEMO()) {
                $this->rpc('Chat.checkOMEMOState', $jid);
            }
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
                $this->rpc('MovimUtils.pushSoftState', $this->route('chat', [$room, 'room']));
                $this->rpc('MovimTpl.fill', '#chat_widget', $this->prepareChat($room, true));

                $chatStates = ChatStates::getInstance();
                $this->onChatState($chatStates->getState($room), false);

                $this->rpc('MovimTpl.showPanel');
                $this->rpc('Chat.focus');

                (new Dictaphone)->ajaxHttpGet();
            }

            if (CurrentCall::getInstance()->isStarted()) {
                $this->rpc('MovimVisio.moveToChat', CurrentCall::getInstance()->getBareJid());
            }

            $this->rpc('Chat.setObservers');
            $this->rpc('MovimTpl.fill', '#' . cleanupId($room) . '-conversation', '');
            $this->getMessages($room, muc: true);
            $this->rpc('Notif.current', 'chat|' . $room);
            $this->rpc('Chat.scrollToSeparator');

            if ($this->user->hasOMEMO() && $conference->isGroupChat()) {
                $this->rpc('Chat.setGroupChatMembers', $conference->members->pluck('jid')->toArray());
                $this->rpc('Chat.checkOMEMOState', $room, true);
            } else {
                $this->rpc('Chat.setGroupChatMembers', []);
            }
        } else {
            $this->rpc('RoomsUtils_ajaxAdd', $room);
            $this->ajaxHttpGetEmpty();
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
            $valid = $messageFile->import($file);

            if (!$valid) $messageFile = null;
        } else {
            try {
                $url = new Url;
                $cache = $url->resolve(trim($message), now: true);

                if ($cache && $url->file !== null) {
                    $messageFile = $url->file;
                }
            } catch (\Exception $e) {
            }
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
    public function sendMessage(
        string $to,
        string $message = '',
        bool $muc = false,
        ?Message $replace = null,
        ?MessageFile $file = null,
        ?int $replyToMid = 0,
        ?bool $mucReceipts = false,
        ?MessageOMEMOHeader $messageOMEMOHeader = null
    ) {
        $tempId = null;

        if ($messageOMEMOHeader) {
            $tempId = $message;
            $message = 'Encrypted OMEMO message sent';
        }

        $body = ($file != null && $file->type != 'xmpp/uri')
            ? $file->url
            : $message;

        if ($body == '' || $body == '/me') {
            return;
        }

        $m = new \App\Message;
        $m->id          = generateUUID();
        $m->originid    = $m->id;
        $m->messageid   = $m->id;
        $m->replaceid   = $replace ? $replace->originid : null;
        $m->user_id     = $this->user->id;
        $m->jidto       = $to;
        $m->jidfrom     = $this->user->id;
        $m->published   = gmdate('Y-m-d H:i:s');

        $reply = null;

        // If the replaced message is quoting another one ensure that we keep the quote
        if ($replace && $replace->parentmid) {
            $replyToMid = $replace->parentmid;
        }

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
            $m->resource    = $this->user->username;
            $m->jidfrom     = $to;
        }

        $p = new Publish;
        $p->setTo($to);
        $p->setReplace($m->replaceid);
        $p->setId($m->id);
        $p->setOriginid($m->originid);

        if ($muc) {
            $p->setMuc();

            if ($mucReceipts) {
                $p->setMucReceipts();
            }
        }

        if ($file) {
            $p->setFile($file);
            $m->resolved = true;
            $m->picture = $file->isPicture;

            if ($file->type == 'xmpp/uri') {
                $xmppUri = new XMPPUri($file->url);

                if ($post = $xmppUri->getPost()) {
                    $m->postid = $post->id;
                }
            }
        }

        if ($reply) {
            $quotable = false;

            // https://xmpp.org/extensions/xep-0461.html#business-id
            if ($reply->isMuc()) {
                // stanza-id only
                $p->setReplyid($reply->stanzaid);
                $quotable = true;
            } elseif ($reply->messageid) {
                $p->setReplyid($reply->messageid);
                $quotable = true;
            }

            if ($quotable) {
                $p->setReplyto($reply->jidfrom . '/' . $reply->resource);
                $matches = [];
                preg_match_all('/^/m', $reply->body, $matches);

                $p->setReplyquotedbodylength(
                    mb_strlen($reply->body) + (2 * count($matches[0])) + 1
                );

                // Prepend quoted message body
                $quotedBody = preg_replace('/^/m', "> ", $reply->body);
                $p->setContent($quotedBody . "\n" . $body);
            } else {
                $p->setContent($body);
            }
        } else {
            $p->setContent($body);
        }

        $m->body = $body;

        // Custom emojis

        $matchedCustomEmojis = [];
        preg_match_all('/:([a-z0-9\-]+):/', $m->body, $matchedCustomEmojis);

        if (!empty($matchedCustomEmojis[1])) {
            $favoritesEmojis = $this->user->emojis->keyBy('pivot.alias');

            $html = '<p>' . $m->body . '</p>';

            $replaced = false;
            $inlines = [];

            // We send the original body without changes
            $p->setContent($m->body);

            foreach ($matchedCustomEmojis[1] as $matched) {
                if ($favoritesEmojis->has($matched)) {
                    $emoji = $favoritesEmojis->get($matched);
                    $replaced = true;

                    $key = generateKey(12);
                    $inlines[$key] = [
                        'hash' => $emoji->cache_hash,
                        'algorythm' => $emoji->cache_hash_algorythm,
                        'alt' => $emoji->pivot->alias,
                    ];

                    $m->body = str_replace(
                        ':' . $matched . ':',
                        Message::$inlinePlaceholder . $key,
                        $m->body
                    );

                    $dom = new \DOMDocument('1.0', 'UTF-8');
                    $img = $dom->createElement('img');
                    $img->setAttribute('src', 'cid:' . \phpToIANAHash()[$emoji->cache_hash_algorythm] . '+' . $emoji->cache_hash . '@bob.xmpp.org');
                    $img->setAttribute('alt', ':' . $emoji->pivot->alias . ':');
                    $dom->append($img);

                    $html = str_replace(
                        ':' . $matched . ':',
                        $dom->saveXML($dom->documentElement),
                        $html
                    );
                }
            }

            if ($replaced) {
                $m->inlines = serialize($inlines);
                $p->setHTML($html);
            }
        }

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

            if ($file) {
                $file->message_mid = $m->mid;
                $file->save();

                $m = $m->fresh();
            }

            $packet = new \Moxl\Xec\Payload\Packet;
            $packet->content = $m;

            // We refresh the Chats list
            $c = new Chats();
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
            if ($parentMessage->isMuc()) {
                $mucPresence = $this->user->session->presences()
                    ->where('jid', $parentMessage->jidfrom)
                    ->where('mucjid', $this->user->id)
                    ->where('muc', true)
                    ->first();

                if (!$mucPresence) return;
            }

            $jidfrom = ($parentMessage->isMuc())
                ? $mucPresence->resource
                : $this->user->id;

            $emojis = $parentMessage->reactions()
                ->where('jidfrom', $jidfrom)
                ->get();

            $r = new Reactions;
            $newEmojis = [];

            // This reaction was not published yet
            if ($emojis->where('emoji', $emoji)->count() == 0) {
                $now = \Carbon\Carbon::now();

                $reaction = new Reaction;
                $reaction->message_mid = $parentMessage->mid;
                $reaction->jidfrom = ($parentMessage->isMuc())
                    ? $this->user->username
                    : $this->user->id;
                $reaction->created_at = $now;
                $reaction->updated_at = $now;
                $reaction->emoji = $emoji;

                if (!$parentMessage->isMuc()) {
                    $reaction->save();
                }

                $newEmojis = $emojis->push($reaction);
            } else {
                if (!$parentMessage->isMuc()) {
                    $parentMessage->reactions()
                        ->where('jidfrom', $jidfrom)
                        ->where('emoji', $emoji)
                        ->delete();
                }

                $newEmojis = $emojis->filter(fn($value, $key) => $value->emoji != $emoji);
            }

            $r->setTo($parentMessage->jidfrom != $parentMessage->user_id
                ? $parentMessage->jidfrom
                : $parentMessage->jidto)
                ->setId(\generateUUID())
                // https://xmpp.org/extensions/xep-0444.html#business-id
                ->setParentid(!$parentMessage->isMuc() && $parentMessage->messageid
                    ? $parentMessage->messageid
                    : $parentMessage->stanzaid)
                ->setReactions($newEmojis->pluck('emoji')->toArray());

            if ($parentMessage->isMuc()) {
                $r->setMuc();
            }

            $r->request();

            if (!$parentMessage->isMuc()) {
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
    public function ajaxLast(string $to, $muc = false)
    {
        $m = Message::getLast($to, $muc);

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

        $m->body = $m->getInlinedBodyAttribute(true);

        if (
            $m
            && !isset($m->sticker_cid)
            && !isset($m->file)
            && !empty($m->body)
        ) {
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

        if (
            $m
            && !isset($m->sticker_cid)
            && !isset($m->file)
        ) {
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
        $message = $this->user->messages()
            ->where('mid', $mid)
            ->first();

        if (
            $message->isClassic()
            && (($message->isMuc() && $message->stanzaid)
                || (!$message->isMuc() && $message->messageid))
        ) {
            $view = $this->tpl();
            $view->assign('message', $message);
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
     * @brief Get a specific message context
     */
    public function ajaxGetMessageContext(string $jid, int $mid)
    {
        if (!validateJid($jid)) return;

        $contextMessage = \App\Message::jid($jid)
            ->where('published', '<=', function ($query) use ($mid) {
                $query->select('published')
                    ->from('messages')
                    ->where('mid', $mid);
            })
            ->orderBy('published', 'desc')
            ->take(1)
            ->skip(3)
            ->first();

        if ($contextMessage) {
            $this->rpc('MovimTpl.fill', '#' . cleanupId($jid) . '-conversation', '');
            $this->ajaxGetHistory($jid, $contextMessage->published, muc: $contextMessage->isMuc(), prepend: false, tryMam: false);
            $this->rpc('Chat.scrollAndBlinkMessageMid', $mid);
            $this->rpc('MovimUtils.addClass', '#chat_widget .contained', 'history');
        }
    }

    /**
     * @brief Get the chat history
     *
     * @param string jid
     * @param string time
     */
    public function ajaxGetHistory(string $jid, ?string $date = null, bool $muc = false, bool $prepend = true, bool $tryMam = true)
    {
        if (!validateJid($jid)) return;

        $messages = \App\Message::jid($jid);

        if ($date !== null) {
            $messages = $messages->where('published', $prepend ? '<' : '>=', date(MOVIM_SQL_DATE, strtotime($date)));
        }

        $messages = $muc
            ? $messages->whereIn('type', Message::MESSAGE_TYPE_MUC)->whereNull('subject')
            : $messages->whereIn('type', Message::MESSAGE_TYPE);

        $messages = $messages->orderBy('published', $prepend ? 'desc' : 'asc')
            ->withCount('reactions')
            ->take($this->_pagination)
            ->get();

        if ($messages->count() > 0) {
            foreach ($messages as $message) {
                $this->prepareMessage($message);
            }

            $this->rpc('Chat.appendMessagesWrapper', $this->_wrapper, $prepend);
            $this->_wrapper = [];
        }

        // Not enough messages from the DB, lets try to get more from MAM
        if ($tryMam && ($messages->count() == 0 || $messages->count() < $this->_pagination)) {
            $this->rpc('MovimUtils.addClass', '#chat_widget .contained', 'loading');

            if ($muc) {
                $this->rpc('RoomsUtils_ajaxGetMAMHistory', $jid);
            } else {
                $this->rpc('Chats_ajaxGetMAMHistory', $jid);
            }
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
    public function ajaxSetRoomConfig(\stdClass $data, $room)
    {
        if (!validateJid($room)) {
            return;
        }

        $sc = new SetConfig;
        $sc->setTo($room)
            ->setData(formToArray($data))
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

        if (
            $message
            && $message->markable == true
            && $message->displayed == null
        ) {
            $message->displayed = gmdate('Y-m-d H:i:s');
            $message->save();

            if (!$message->isMuc()) {
                \Moxl\Stanza\Message::displayed(
                    $jid,
                    $message->messageid,
                    $message->type
                );
            }
            // https://xmpp.org/extensions/xep-0333.html#rules-muc
            elseif ($message->stanzaid) {
                \Moxl\Stanza\Message::displayed(
                    $jid,
                    $message->stanzaid,
                    $message->type
                );
            }
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
    public function ajaxClearHistoryConfirm(string $jid)
    {
        if (!validateJid($jid)) {
            return;
        }

        \App\Message::whereIn('id', function ($query) use ($jid) {
            $jidFromToMessages = DB::table('messages')
                ->where('user_id', $this->user->id)
                ->where('jidfrom', $jid)
                ->unionAll(
                    DB::table('messages')
                        ->where('user_id', $this->user->id)
                        ->where('jidto', $jid)
                );

            $query->select('id')->from(
                $jidFromToMessages,
                'messages'
            )->where('user_id', $this->user->id);
        })->delete();

        $this->user->MAMEarliests()->where('jid', $jid)->delete();

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

    public function ajaxClearAndGetMessages(string $jid, $muc = false)
    {
        $this->rpc('MovimTpl.fill', '#' . cleanupId($jid) . '-conversation', '');
        $this->getMessages($jid, $muc);
        $this->rpc('MovimUtils.removeClass', '#chat_widget .contained', 'history');
    }

    public function getMessages(string $jid, $muc = false, $seenOnly = false, $event = true)
    {
        if (!validateJid($jid)) {
            return;
        }

        $messagesQuery = \App\Message::jid($jid);

        $messagesQuery = $muc
            ? $messagesQuery->whereIn('type', Message::MESSAGE_TYPE_MUC)->whereNull('subject')
            : $messagesQuery->whereIn('type', Message::MESSAGE_TYPE);

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
        if ($firstMessage && $firstMessage->isMuc()) {
            $this->_mucPresences = $this->user->session->presences()
                ->where('jid', $firstMessage->jidfrom)
                ->where('muc', true)
                ->whereIn('resource', $messages->pluck('resource')->unique())
                ->get()
                ->keyBy(function ($presence) {
                    return $presence->jid . $presence->resource;
                });
        }

        if (!$seenOnly) {
            $messages = $messages->reverse();

            foreach ($messages as $message) {
                $this->prepareMessage($message);
            }

            $view = $this->tpl();
            $view->assign('jid', $jid);

            $view->assign('contact', Contact::firstOrNew(['id' => $jid]));
            $view->assign('me', false);
            $view->assign('muc', $muc);
            $left = $view->draw('_chat_bubble');

            $view->assign('contact', Contact::firstOrNew(['id' => $this->user->id]));
            $view->assign('me', true);
            $view->assign('muc', $muc);
            $right = $view->draw('_chat_bubble');

            $this->rpc('Chat.setSpecificElements', $left, $right);
            $this->rpc('Chat.appendMessagesWrapper', $this->_wrapper, false);
        }

        if ($event) {
            $this->event($muc ? 'chat_open_room' : 'chat_open', $jid);
        }

        $this->event('chat_counter', $this->user->unreads());

        if ($unreadsCount > 0) {
            $this->rpc('Chat.insertSeparator', $unreadsCount);
        }

        // Do we need to query MAM?
        if ($messages->isEmpty()) {
            $earliest = MAMEarliest::query();
            $earliest = $muc ? $earliest->where('to', $jid)
                : $earliest->where('jid', $jid);

            if (!$earliest->first()) {
                $this->rpc('Chat.getHistory', true);
            }
        } elseif ($messages->count() < $this->_pagination) {
            $earliest = MAMEarliest::query();
            $earliest = $muc ? $earliest->where('to', $jid)
                : $earliest->where('jid', $jid);
            $me = $earliest->first();

            if (
                !$me ||
                (new Carbon($me->earliest))->isAfter(new Carbon($messages->first()->published))
            ) {
                $this->rpc('Chat.getHistory', true);
            }
        }
    }

    public function prepareMessage(&$message, $jid = null): array
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
            $message->body = '<i class="material-symbols">delete</i> ' . __('message.retracted');
        } elseif ($message->encrypted) {
            $message->body = __('message.encrypted');
        } elseif (isset($message->html) && !isset($message->file)) {
            $message->body = $message->html;
        } elseif (!isset($message->file)) {
            $message->addUrls();

            if (is_string($message->body)) {
                $message->body = $emoji->replace($message->body);
            }
        }

        if (isset($message->subject) && $message->type == 'headline') {
            $message->body = $message->subject . ': ' . $message->body;
        }

        // XEP-0393
        if (!empty($message->body)) {
            $message->body = (preg_replace('/^```(\n*)([\s\S]*?)```([A-Za-z \t]*)*$/m', "<code class='block'>$2</code>", $message->body));
            $message->body = (preg_replace('/(?<=^|[\s,\*,_,~])(`(?!\s).+?(?<!\s)`)/', "<code>$1</code>", $message->body));
            $message->body = (preg_replace('/(?<=^|[\s,_,`,~])(\*(?!\s).+?(?<!\s)\*)/', "<b>$1</b>", $message->body));
            $message->body = (preg_replace('/(?<=^|[\s,\*,`,~])(_(?!\s).+?(?<!\s)_)/', "<em>$1</em>", $message->body));
            $message->body = (preg_replace('/(?<=^|[\s,\*,_,`])(~(?!\s).+?(?<!\s)~)/', "<s>$1</s>", $message->body));
        }

        // Inlines
        $message->body = $message->getInlinedBodyAttribute(false, true) ?? $message->body;

        // Sticker message
        if (isset($message->sticker_cid_hash) && isset($message->sticker_cid_algorythm)) {
            $stickerImage = $message->stickerImage;

            if (
                !$stickerImage
                && $message->jidfrom != $message->session
            ) {
                $r = new Request;
                $r->setTo($message->jidfrom)
                    ->setResource($message->resource)
                    ->setHash($message->sticker_cid_hash)
                    ->setAlgorythm($message->sticker_cid_algorythm)
                    ->setMessagemid($message->mid)
                    ->request();
            } else {
                $stickerSize = $stickerImage->getGeometry();

                $message->sticker = [
                    'url' => Image::getOrCreate($stickerImage->getKey()), // Todo, don't reload
                    'width' => $stickerSize['width'],
                    'height' => $stickerSize['height']
                ];
            }

            $message->body = '';
        }

        // Jumbo emoji
        if (
            $emoji->isSingleEmoji()
            && !isset($message->html)
            && $message->isClassic()
        ) {
            $message->sticker = [
                'url' => $emoji->getLastSingleEmojiURL(),
                'title' => ':' . $emoji->getLastSingleEmojiTitle() . ':',
                'height' => 60,
            ];

            $message->body = '';
        }

        // Attached file
        if (!$message->retracted) {
            if ($message->postid != null) {
                $post = $message->post()->first();

                if ($post->isStory()) {
                    $story = AppPost::myStories($message->postid)->first();

                    if ($story) {
                        $p = new Post();
                        $message->card = $p->prepareTicket($story);
                        $message->story = true;
                    } else {
                        $view = $this->tpl();
                        $view->assign('post', $post);
                        $message->body = $view->draw('_chat_story_forbidden');
                    }
                } else {
                    $p = new Post();
                    $message->card = $p->prepareTicket($post);

                    if ($message->body == null) {
                        $message->body = '';
                    }
                }
            } elseif (isset($message->file) && $message->file->type != 'xmpp') {
                $message->body = '';
            }
        }

        if (
            $message->resolvedUrl && !$message->file
            && !$message->card && !$message->sticker
        ) {
            $resolved = $message->resolvedUrl->cache;
            if ($resolved) {
                $message->card =  $this->prepareEmbed($resolved);
            }

            if ($message->body == null) {
                $message->body = '';
            }
        }

        if ($message->retracted) {
            $message->card = null;
        }

        // Parent
        if ($message->parent) {
            if ($message->parent->file) {
                $message->parent->body = '<i class="material-symbols">insert_drive_file</i> ' . __('avatar.file');

                if (typeIsPicture($message->parent->file->type)) {
                    $message->parent->body = '<i class="material-symbols">image</i> ' . __('chats.picture');
                } elseif (typeIsAudio($message->parent->file->type)) {
                    $message->parent->body = '<i class="material-symbols">equalizer</i> ' . __('chats.audio');
                } elseif (typeIsVideo($message->parent->file->type)) {
                    $message->parent->body = '<i class="material-symbols">local_movies</i> ' . __('chats.video');
                }
            }

            if ($message->parent->isMuc()) {
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
        } elseif ($message->body) {
            // Let's try to support "quoted" messages
            $quote = '&gt; ';
            $parent = '';
            $remains = '';
            $endOfQuote = false;

            foreach (explode(PHP_EOL, $message->body) as $line) {
                if (substr($line, 0, strlen($quote)) == $quote && $endOfQuote == false) {
                    $parent .= substr($line, strlen($quote)) . "\n";
                } else {
                    $endOfQuote = true;
                    $remains .= $line . "\n";
                }
            }

            if ($parent !== '') {
                $message->parentQuote = $parent;
                $message->body = trim($remains);
            }
        }

        // reactions_count if cached, if not, reload it from the DB
        if ($message->reactions_count ?? $message->reactions()->count()) {
            $message->reactionsHtml = $this->prepareReactions($message);
        }

        if ($message->body) {
            $message->rtl = isRTL($message->body);
        }

        $message->publishedPrepared = prepareTime($message->published);

        if ($message->delivered) {
            $message->delivered = prepareDate($message->delivered, true);
        }

        if ($message->displayed) {
            $message->displayed = prepareDate($message->displayed, true);
        }

        $date = prepareDate($message->published, false, false, true);

        if (empty($date)) {
            $date = $this->__('date.today');
        }

        // We create the date wrapper
        if (!array_key_exists($date, $this->_wrapper)) {
            $this->_wrapper[$date] = [];
        }

        $messageDBSeen = $message->seen;
        $n = new Notif;

        if ($message->isMuc()) {
            $message->resolveColor();

            // Cache the resolved presences for a while
            $key = $message->jidfrom . $message->resource;
            if (!isset($this->_mucPresences[$key])) {
                $this->_mucPresences[$key] = $this->user->session->presences()
                    ->where('jid', $message->jidfrom)
                    ->where('resource', $message->resource)
                    ->where('muc', true)
                    ->first();
            }

            if ($this->_mucPresences[$key] && $this->_mucPresences[$key] !== true) {
                $message->moderator = ($this->_mucPresences[$key]->mucrole == 'moderator');
                $message->mucjid = $this->_mucPresences[$key]->mucjid;
                $message->mine = $message->seen = ($this->_mucPresences[$key]->mucjid == $this->user->id);
                $message->icon_url = $this->_mucPresences[$key]->conferencePicture;
            } else {
                // No presence, we set a placeholder avatar as a fallback
                $message->icon_url = avatarPlaceholder($message->resource ?? $message->jidfrom);
                $this->_mucPresences[$key] = true;
            }
        }

        // Only used for message replacement
        $message->originid = $message->originid == null && !$message->isMuc()
            ? $message->messageid
            : null;

        // Handle faulty replacing messages
        if (
            $message->replace
            && (
                ($message->replace->jidfrom != $message->jidfrom || $message->replace->resource != $message->resource)
                ||
                ($message->isMuc() && !$this->_mucPresences[$message->jidfrom . $message->resource])
            )
        ) {
            unset($message->replace);
            unset($message->replaceid);
        }

        if ($message->seen === false) {
            $message->seen = ('chat|' . $message->jidfrom == $n->getCurrent());
        }

        if (
            $message->seen === true
            && $messageDBSeen === false
        ) {
            $this->user->messages()
                ->where('id', $message->id)
                ->update(['seen' => true]);
        }

        $msgkey = '<' . $message->jidfrom;
        $msgkey .= ($message->isMuc() && $message->resource != null)
            ? cleanupId($message->resource, true)
            : '';
        $msgkey .= '>' . substr($message->published, 11, 5);

        $counter = count($this->_wrapper[$date]);

        $this->_wrapper[$date][$counter . $msgkey] = $message;

        if ($message->type == 'invitation') {
            $view = $this->tpl();
            $view->assign('message', $message);
            $message->body = ($message->jidfrom == $this->user->id)
                ? $view->draw('_chat_invitation_self')
                : $view->draw('_chat_invitation');
        }

        // Internal messages
        if (in_array($message->type, [
            'jingle_finish',
            'jingle_incoming',
            'jingle_outgoing',
            'jingle_reject',
            'jingle_retract',
            'muc_admin',
            'muc_member',
            'muc_outcast',
            'muc_owner',
            'muji_propose',
        ])) {
            $view = $this->tpl();
            $view->assign('message', $message);
            $message->body = $view->draw('_chat_' . $message->type);
        }

        if ($message->type == 'jingle_end') {
            $view = $this->tpl();
            $view->assign('message', $message);
            $view->assign('diff', false);

            $start = $this->user->messages()->where('thread', $message->thread)
                ->whereIn('type', ['jingle_incoming', 'jingle_outgoing'])
                ->first();

            if ($start) {
                $diff = (new \DateTime($start->created_at))
                    ->diff(new \DateTime($message->created_at));

                $view->assign('diff', $diff);
            }

            $message->body = trim((string)$view->draw('_chat_jingle_end'));
        }

        if ($message->type == 'muji_retract') {
            $view = $this->tpl();
            $view->assign('message', $message);
            $view->assign('diff', false);

            $start = $this->user->messages()->where('thread', $message->thread)
                ->whereIn('type', ['muji_propose'])
                ->first();

            if ($start) {
                $diff = (new \DateTime($start->created_at))
                    ->diff(new \DateTime($message->created_at));

                $view->assign('diff', $diff);
            }

            $message->body = trim((string)$view->draw('_chat_muji_retract'));
        }

        return $this->_wrapper;
    }

    public function prepareEmbed(EmbedLight $embed, ?Message $message = null)
    {
        $tpl = $this->tpl();
        $tpl->assign('embed', $embed);
        $tpl->assign('message', $message);
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
        $view->assign('contactincall', CurrentCall::getInstance()->isJidInCall($jid));
        $view->assign('incall', CurrentCall::getInstance()->isStarted());
        $view->assign(
            'info',
            \App\Info::where('server', $this->user->session->host)
                ->where('node', '')
                ->first()
        );
        $view->assign('anon', false);
        $view->assign('counter', $this->user->unreads(null, false, true));

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
            $view->assign('contact', Contact::firstOrNew(['id' => $jid]));
        }

        return $view->draw('_chat_header');
    }

    public function prepareEmpty()
    {
        $view = $this->tpl();
        $view->assign('top', $this->user->session->topContactsToChat()->take(15)->get());

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

        $users = Contact::suggest()
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
