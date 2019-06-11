<?php

use Moxl\Xec\Action\Message\Publish;

use Moxl\Xec\Action\Muc\GetConfig;
use Moxl\Xec\Action\Muc\SetConfig;

use App\Configuration;
use App\Message;
use App\Reaction;

use Moxl\Xec\Action\BOB\Request;

use Respect\Validation\Validator;

use Illuminate\Database\Capsule\Manager as DB;

use Movim\Picture;
use Movim\Session;
use Movim\ChatStates;
use Movim\ChatOwnState;

include_once WIDGETS_PATH.'ContactActions/ContactActions.php';

class Chat extends \Movim\Widget\Base
{
    private $_pagination = 50;
    private $_wrapper = [];
    private $_mucPresences = [];
    private $_messageTypes = ['chat', 'headline', 'invitation', 'jingle_start', 'jingle_end'];

    public function load()
    {
        $this->addjs('chat.js');
        $this->addcss('chat.css');
        $this->registerEvent('invitation', 'onMessage');
        $this->registerEvent('carbons', 'onMessage');
        $this->registerEvent('message', 'onMessage');
        $this->registerEvent('receiptack', 'onMessageReceipt');
        $this->registerEvent('displayed', 'onMessage', 'chat');
        $this->registerEvent('mam_get_handle', 'onMAMRetrieved', 'chat');
        $this->registerEvent('composing', 'onComposing', 'chat');
        $this->registerEvent('paused', 'onPaused', 'chat');
        $this->registerEvent('subject', 'onConferenceSubject', 'chat');
        $this->registerEvent('muc_setsubject_handle', 'onConferenceSubject', 'chat');
        $this->registerEvent('muc_getconfig_handle', 'onRoomConfig', 'chat');
        $this->registerEvent('muc_setconfig_handle', 'onRoomConfigSaved', 'chat');
        $this->registerEvent('muc_setconfig_error', 'onRoomConfigError', 'chat');
        $this->registerEvent('presence_muc_handle', 'onMucConnected', 'chat');

        $this->registerEvent('bob_request_handle', 'onSticker');
        $this->registerEvent('notification_counter_clear', 'onNotificationCounterClear');
    }

    public function onMessageReceipt($packet)
    {
        $this->onMessage($packet, false, true);
    }

    public function onNotificationCounterClear($params)
    {
        list($page, $first, $room) = array_pad($params, 3, null);

        if ($page === 'chat') {
            $this->prepareMessages($first, ($room === 'room'));
        }
    }

    public function onMessage($packet, $history = false, $receipt = false)
    {
        $message = $packet->content;
        $from = null;
        $chatStates = ChatStates::getInstance();

        if ($message->isEmpty()) {
            return;
        }

        if ($message->user_id == $message->jidto
        && !$history
        && $message->jidfrom != $message->jidto) {
            $from = $message->jidfrom;
            $roster = $this->user->session->contacts()->where('jid', $from)->first();
            $contact = App\Contact::firstOrNew(['id' => $from]);

            if ($contact != null
            //&& $message->isTrusted()
            && !$message->isOTR()
            && $message->type != 'groupchat'
            && !$message->oldid) {
                $chatStates->clearState($from);

                Notification::append(
                    'chat|'.$from,
                    $roster ? $roster->truename : $contact->truename,
                    $message->body,
                    $contact->getPhoto(),
                    4,
                    $this->route('chat', $contact->jid)
                );
            }
            // If it's a groupchat message
            elseif ($message->type == 'groupchat'
                   && $message->quoted
                   && !$receipt) {
                $conference = $this->user->session
                                   ->conferences()->where('conference', $from)
                                   ->first();

                Notification::append(
                    'chat|'.$from,
                    ($conference != null && $conference->name)
                        ? $conference->name
                        : $from,
                    $message->resource.': '.$message->body,
                    false,
                    4
                );
            } elseif ($message->type == 'groupchat') {
                $chatStates->clearState($from, $message->resource);
            }

            $this->onPaused($chatStates->getState($from));
        }

        if (!$message->isOTR()) {
            $this->rpc('Chat.appendMessagesWrapper', $this->prepareMessage($message, $from));
        }

        $this->event('chat_counter', $this->user->unreads());
    }

    public function onSticker($packet)
    {
        list($to, $cid) = array_values($packet->content);
        $this->ajaxGet($to);
    }

    public function onComposing(array $array)
    {
        $this->setState(
            $array[0],
            is_array($array[1]) && !empty($array[1])
                ? $this->prepareComposeList(array_keys($array[1]))
                : $this->__('message.composing')
        );
    }

    public function onPaused(array $array)
    {
        $this->setState(
            $array[0],
            is_array($array[1]) && !empty($array[1])
                ? $this->prepareComposeList(array_keys($array[1]))
                : ''
        );
    }

    public function onConferenceSubject($packet)
    {
        $this->ajaxGetRoom($packet->content->jidfrom);
    }

    public function onMAMRetrieved($packet)
    {
        $this->ajaxGetRoom($packet->content);
    }

    public function onMucConnected($packet)
    {
        $this->ajaxGetRoom($packet->content->jid, false, true);
    }

    public function onRoomConfigError($packet)
    {
        Notification::toast($packet->content);
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
        Notification::toast($this->__('chatroom.config_saved'));
    }

    private function setState(string $jid, string $message)
    {
        $this->rpc('MovimTpl.fill', '#' . cleanupId($jid.'_state'), $message);
    }

    public function ajaxInit()
    {
        $view = $this->tpl();
        $date = $view->draw('_chat_date');
        $separator = $view->draw('_chat_separator');

        $this->rpc('Chat.setGeneralElements', $date, $separator);
    }

    public function ajaxClearCounter($jid)
    {
        $this->prepareMessages($jid, false, true);
        $this->event('chat_counter', $this->user->unreads());
    }

    /**
     * @brief Get a discussion
     * @param string $jid
     */
    public function ajaxGet($jid = null, $light = false)
    {
        if ($jid == null) {
            $this->rpc('Notification.current', 'chat');
            $this->rpc('MovimUtils.pushState', $this->route('chat'));
            $this->rpc('MovimTpl.fill', '#chat_widget', $this->prepareEmpty());
            $this->rpc('MovimTpl.hidePanel');
        } else {
            if ($light == false) {
                $this->rpc('MovimUtils.pushState', $this->route('chat', $jid));
                $this->rpc('MovimTpl.fill', '#chat_widget', $this->prepareChat($jid));
                $this->rpc('MovimTpl.showPanel');
                $this->rpc('Chat.focus');
            }

            $this->prepareMessages($jid);
            $this->rpc('Notification.current', 'chat|'.$jid);
        }
    }

    /**
     * @brief Get a chatroom
     * @param string $jid
     */
    public function ajaxGetRoom($room, $light = false, $noConnect = false)
    {
        if (!$this->validateJid($room)) {
            return;
        }

        $r = $this->user->session->conferences()->where('conference', $room)->first();

        if ($r) {
            if (!$r->connected && !$noConnect) {
                $this->rpc('Rooms_ajaxJoin', $r->conference, $r->nick);
            }

            if ($light == false) {
                $this->rpc('MovimUtils.pushState', $this->route('chat', [$room, 'room']));
                $this->rpc('MovimTpl.fill', '#chat_widget', $this->prepareChat($room, true));
                $this->rpc('MovimTpl.showPanel');
                $this->rpc('Chat.focus');
            }

            $this->prepareMessages($room, true);
            $this->rpc('Notification.current', 'chat|'.$room.'|room');
        } else {
            $this->rpc('Rooms_ajaxAdd', $room);
        }
    }

    /**
     * @brief Get a Drawer view of a contact
     */
    public function ajaxGetContact($jid)
    {
        $c = new ContactActions;
        $c->ajaxGetDrawer($jid);
    }

    /**
     * @brief Send a message
     *
     * @param string $to
     * @param string $message
     * @return void
     */
    public function ajaxHttpSendMessage($to, $message = false, $muc = false, $resource = false, $replace = false, $file = false, $attachId = false)
    {
        $message = trim($message);
        if (filter_var($message, FILTER_VALIDATE_URL)) {
            $headers = requestHeaders($message);

            if ($headers['http_code'] == 200
            && isset($headers['content_type'])
            && typeIsPicture($headers['content_type'])
            && $headers['download_content_length'] > 100) {
                $file = new \stdClass;
                $file->name = $message;
                $file->type = $headers['content_type'];
                $file->size = $headers['download_content_length'];
                $file->uri  = $message;
            }
        }

        $body = ($file != false && $file->type != 'xmpp')
            ? $file->uri
            : $message;

        if ($body == '' || $body == '/me') {
            return;
        }

        $oldid = null;

        if ($replace) {
            $oldid = $replace->id;

            $m = $replace;
            $m->id = generateUUID();

            \App\Message::where('id', $oldid)->update([
                'id' => $m->id,
                'replaceid' => $m->id
            ]);
        } else {
            $m = new \App\Message;
            $m->id          = generateUUID();
            $m->replaceid   = $m->id;
            $m->user_id     = $this->user->id;
            $m->jidto       = echapJid($to);
            $m->jidfrom     = $this->user->id;
            $m->published   = gmdate('Y-m-d H:i:s');
        }

        // TODO: make this boolean configurable
        $m->markable = true;
        $m->seen = true;

        $m->type    = 'chat';
        $m->resource = $this->user->session->resource;

        if ($muc) {
            $m->type        = 'groupchat';
            $m->resource    = $this->user->session->username;
            $m->jidfrom     = $to;
        }

        $m->body      = $body;

        if ($resource != false) {
            $to = $to . '/' . $resource;
        }

        // We decode URL codes to send the correct message to the XMPP server
        $p = new Publish;
        $p->setTo($to);
        //$p->setHTML($m->html);
        $p->setContent($m->body);

        if ($replace != false) {
            $p->setReplace($oldid);
        }

        $p->setId($m->id);

        if ($muc) {
            $p->setMuc();
        }

        if ($file) {
            $m->file = (array)$file;
            $p->setFile($file);
        }

        if ($attachId) {
            $parentMessage = $this->user->messages()
                            ->where('replaceid', $attachId)
                            ->first();

            if ($parentMessage) {
                if (!$p->getMuc()) {
                    $reaction = new Reaction;
                    $reaction->message_mid = $parentMessage->mid;
                    $reaction->jidfrom = ($muc)
                        ? $this->user->session->username
                        : $this->user->id;
                    $reaction->emoji = $body;
                    $reaction->save();
                }

                $p->setAttachId($attachId);

                $m = $parentMessage;
            }
        }

        (ChatOwnState::getInstance())->halt();

        $p->request();

        /* Is it really clean ? */
        if (!$p->getMuc()) {
            if ($attachId == false) {
                $m->oldid = $oldid;
                $m->body = htmlentities(trim($m->body), ENT_XML1, 'UTF-8');
                $m->save();
                $m = $m->fresh();
            }

            $packet = new \Moxl\Xec\Payload\Packet;
            $packet->content = $m;
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
    public function ajaxHttpCorrect($to, $message)
    {
        $replace = $this->user->messages()
                        ->where('jidto', $to)
                        ->orderBy('published', 'desc')
                        ->first();

        if ($replace) {
            $this->ajaxHttpSendMessage($to, $message, false, false, $replace);
        }
    }

    /**
     * @brief Send a reaction
     *
     * @
     */
    public function ajaxHttpSendReaction(string $mid, string $emoji)
    {
        $message = $this->user->messages()
                        ->where('mid', $mid)
                        ->first();

        if ($message) {
            // Try to load the MUC presence and resolve the resource
            $mucPresence = null;
            if ($message->type == 'groupchat') {
                $mucPresence = $this->user->session->presences()
                                    ->where('jid', $message->jidfrom)
                                    ->where('mucjid', $this->user->id)
                                    ->where('muc', true)
                                    ->first();

                if (!$mucPresence) return;
            }

            if ($message->reactions()
                        ->where('emoji', $emoji)
                        ->where('jidfrom', ($message->type == 'groupchat')
                            ? $mucPresence->resource
                            : $this->user->id)
                        ->count() == 0) {
                $this->ajaxHttpSendMessage(
                    $message->jidfrom != $message->user_id
                        ? $message->jidfrom
                        : $message->jidto,
                    $emoji, $message->type == 'groupchat',
                    false, false, false, $message->replaceid
                );
            }
        }
    }

    /**
     * @brief Get the last message sent
     *
     * @param string $to
     * @return void
     */
    public function ajaxLast($to)
    {
        $m = $this->user->messages()
                        ->where('jidto', $to)
                        ->orderBy('published', 'desc')
                        ->first();

        if (!isset($m->sticker)
        && !isset($m->file)) {
            $this->rpc('Chat.setTextarea', htmlspecialchars_decode($m->body));
        }
    }

    /**
     * @brief Send a "composing" message
     *
     * @param string $to
     * @return void
     */
    public function ajaxSendComposing($to, $muc = false)
    {
        if (!$this->validateJid($to)) {
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
        if (!$this->validateJid($jid)) {
            return;
        }

        $messages = $this->user->messages()
                         ->where(function ($query) use ($jid) {
                             $query->where('jidfrom', $jid)
                                      ->orWhere('jidto', $jid);
                         })
                         ->where('published', $prepend ? '<' : '>', date(SQL_DATE, strtotime($date)));


        $messages = $muc
            ? $messages->where('type', 'groupchat')->whereNull('subject')
            : $messages->whereIn('type', $this->_messageTypes);

        $messages = $messages->orderBy('published', 'desc')
                             ->take($this->_pagination)
                             ->get();

        if ($messages->count() > 0) {
            if ($prepend) {
                Notification::toast($this->__('message.history', $messages->count()));
            } else {
                $messages = $messages->reverse();
            }

            foreach ($messages as $message) {
                if (!$message->isOTR()) {
                    $this->prepareMessage($message);
                }
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
        if (!$this->validateJid($room)) {
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
        if (!$this->validateJid($room)) {
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
        if (!$this->validateJid($jid)) {
            return;
        }

        $message = $this->user->messages()->where('id', $id)->first();

        if ($message
        && $message->markable == true
        && $message->displayed == null) {
            $message->displayed = gmdate('Y-m-d H:i:s');
            $message->save();

            \Moxl\Stanza\Message::displayed($jid, $message->replaceid);
        }
    }

    /**
     * @brief Clear the history
     *
     * @param string $room
     */
    public function ajaxClearHistory($jid)
    {
        if (!$this->validateJid($jid)) {
            return;
        }

        $this->user->messages()->where(function ($query) use ($jid) {
            $query->where('jidfrom', $jid)
                  ->orWhere('jidto', $jid);
        })->delete();

        $this->ajaxGet($jid);
    }

    public function prepareChat($jid, $muc = false)
    {
        $view = $this->tpl();

        $view->assign('jid', $jid);

        $view->assign('smiley', $this->call('ajaxSmiley'));
        $view->assign('emoji', prepareString('😀'));
        $view->assign('muc', $muc);
        $view->assign('anon', false);
        $view->assign(
            'info',
            \App\Info::where('server', $this->user->session->host)
                     ->where('node', '')
                     ->first()
        );

        if ($muc) {
            $view->assign('room', $jid);
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

        return $view->draw('_chat');
    }

    public function prepareMessages($jid, $muc = false, $seenOnly = false)
    {
        if (!$this->validateJid($jid)) {
            return;
        }

        $jid = echapJid($jid);

        $messages = $this->user->messages()->where(function ($query) use ($jid) {
            $query->where('jidfrom', $jid)
                  ->orWhere('jidto', $jid);
        });

        $messagesQuery = $muc
            ? $messages->where('type', 'groupchat')->whereNull('subject')
            : $messages->whereIn('type', $this->_messageTypes);

        $messages = $messagesQuery->orderBy('published', 'desc')->take($this->_pagination)->get();
        $unreadsCount = $messages->where('seen', false)->count();

        if ($unreadsCount > 0) {
            $messagesQuery->where('seen', false)->update(['seen' => true]);
        }

        if ($seenOnly) return;

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
        $this->rpc('Chat.appendMessagesWrapper', $this->_wrapper, false, true);

        $this->event($muc ? 'chat_open_room' : 'chat_open', $jid);
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

        if (isset($message->html)) {
            $message->body = $message->html;
        } else {
            $message->addUrls();
            $message->body = $emoji->replace($message->body);
            $message->body = addHFR($message->body);
        }

        if (isset($message->subject) && $message->type == 'headline') {
            $message->body = $message->subject .': '. $message->body;
        }

        // Sticker message
        if (isset($message->sticker)) {

            $p = new Picture;
            $sticker = $p->get($message->sticker, false, false, 'png');
            $stickerSize = $p->getSize();

            if ($sticker == false
            && $message->jidfrom != $message->session) {
                $r = new Request;
                $r->setTo($message->jidfrom)
                    ->setResource($message->resource)
                    ->setCid($message->sticker)
                    ->request();
            } else {
                $message->sticker = [
                    'url' => $sticker,
                    'width' => $stickerSize['width'],
                    'height' => $stickerSize['height']
                ];
            }
        }

        // Jumbo emoji
        if ($emoji->isSingleEmoji() && !isset($message->html)) {
            $message->sticker = [
                'url' => $emoji->getLastSingleEmojiURL(),
                'height' => 60
            ];
        }

        // Attached file
        if (isset($message->file)) {
            // We proxify pictures links even if they are advertized as small ones
            if (\array_key_exists('type', $message->file)
            && typeIsPicture($message->file['type'])
            && $message->file['size'] <= SMALL_PICTURE_LIMIT) {
                $message->sticker = [
                    'thumb' => $this->route('picture', urlencode($message->file['uri'])),
                    'url' => $message->file['uri'],
                    'picture' => true
                ];
            }

            $url = parse_url($message->file['uri']);
            // Other image websites
            if (\array_key_exists('host', $url)) {
                switch ($url['host']) {
                    case 'i.imgur.com':
                        $matches = [];
                        preg_match('/https:\/\/i.imgur.com\/([a-zA-Z0-9]{7})(.*)/', $message->file['uri'], $matches);

                        if (!empty($matches)) {
                            $message->sticker = [
                                'url' => $message->file['uri'],
                                'thumb' => 'https://i.imgur.com/' . $matches[1] . 'g' . $matches[2],
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

        // Reactions
        if ($message->reactions()->count()) {
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
            $message->color = stringToColor($message->session_id . $message->resource . $message->type);

            // Cache the resolved presences for a while
            $key = $message->jidfrom.$message->resource;
            if (!isset($this->mucPresences[$key])) {
                $this->mucPresences[$key] = $this->user->session->presences()
                           ->where('jid', $message->jidfrom)
                           ->where('resource', $message->resource)
                           ->where('muc', true)
                           ->first();
            }

            if ($this->mucPresences[$key] && $this->mucPresences[$key] !== true) {
                if ($url = $this->mucPresences[$key]->conferencePicture) {
                    $message->icon_url = $url;
                }

                $message->moderator = ($this->mucPresences[$key]->mucrole == 'moderator');
                $message->mine = $message->seen = ($this->mucPresences[$key]->mucjid == $this->user->id);

            } else {
                $this->mucPresences[$key] = true;
            }

            $message->icon = firstLetterCapitalize($message->resource);

            if ($message->seen === false) {
                $message->seen = ('chat|'.$message->jidfrom.'|room' == $n->getCurrent());
            }
        } else {
            $message->seen = ('chat|'.$message->jidfrom == $n->getCurrent());
        }

        if ($message->seen === true
        && $messageDBSeen === false) {
            $this->user->messages()
                 ->where('id', $message->id)
                 ->update(['seen' => true]);
        }

        $msgkey = '<' . $message->jidfrom;
        $msgkey .= ($message->type == 'groupchat') ? $message->resource : '';
        $msgkey .= '>' . substr($message->published, 11, 5);

        $counter = count($this->_wrapper[$date]);

        $this->_wrapper[$date][$counter.$msgkey] = $message;

        if ($message->type == 'invitation') {
            $view = $this->tpl();
            $view->assign('message', $message);
            $message->body = $view->draw('_chat_invitation');
        }

        if ($message->type == 'jingle_start') {
            $view = $this->tpl();
            $view->assign('message', $message);
            $message->body = $view->draw('_chat_jingle_start');
        }

        if ($message->type == 'jingle_end') {
            $view = $this->tpl();
            $view->assign('message', $message);
            $view->assign('diff', false);

            $start = Message::where(
                [
                    'type' =>'jingle_start',
                    'thread'=> $message->thread
                ]
            )->first();

            if ($start) {
                $diff = (new DateTime($start->created_at))
                  ->diff(new DateTime($message->created_at));

                $view->assign('diff', $diff);
            }

            $message->body = $view->draw('_chat_jingle_end');
        }

        return $this->_wrapper;
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

    public function prepareEmpty()
    {
        $view = $this->tpl();

        $conferences = \App\Info::where('category', 'conference')
                                ->whereNotIn('server', $this->user->session->conferences()->pluck('conference')->toArray())
                                ->where('mucpublic', true)
                                ->where('mucpersistent', true);

        $conferences = (Configuration::get()->restrictsuggestions)
            ? $conferences->where('server', 'like', '%@%.' . $this->user->session->host)
            : $conferences->where('server', 'like', '%@%');

        $conferences = $conferences->orderBy('occupants', 'desc')->take(8)->get();

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
            ->take(8)
            ->get();

        $view->assign('conferences', $conferences);
        $view->assign('top', $top);

        return $view->draw('_chat_empty');
    }

    private function prepareComposeList(array $list)
    {
        $view = $this->tpl();
        $view->assign('list', implode(', ', $list));
        return $view->draw('_chat_compose_list');
    }

    /**
     * @brief Validate the jid
     *
     * @param string $jid
     */
    private function validateJid($jid)
    {
        return (Validator::stringType()->noWhitespace()->length(6, 60)->validate($jid));
    }

    public function getSmileyPath($id)
    {
        return getSmileyPath($id);
    }

    public function display()
    {
        $this->view->assign('pagination', $this->_pagination);
    }
}
