<?php

use Moxl\Xec\Action\Message\Composing;
use Moxl\Xec\Action\Message\Paused;
use Moxl\Xec\Action\Message\Publish;

use Moxl\Xec\Action\Muc\GetConfig;
use Moxl\Xec\Action\Muc\SetConfig;

use App\Configuration;

use Moxl\Xec\Action\BOB\Request;

use Respect\Validation\Validator;

use Illuminate\Database\Capsule\Manager as DB;

use Movim\Picture;

include_once WIDGETS_PATH.'ContactActions/ContactActions.php';

class Chat extends \Movim\Widget\Base
{
    private $_pagination = 50;
    private $_wrapper = [];
    private $_mucPresences = [];

    function load()
    {
        $this->addjs('chat.js');
        $this->addcss('chat.css');
        $this->registerEvent('invitation', 'onMessage');
        $this->registerEvent('carbons', 'onMessage');
        $this->registerEvent('message', 'onMessage');
        $this->registerEvent('receiptack', 'onMessageReceipt');
        $this->registerEvent('displayed', 'onMessage', 'chat');
        //$this->registerEvent('mamresult', 'onMessageHistory', 'chat');
        $this->registerEvent('mam_get_handle', 'onMAMRetrieved', 'chat');
        $this->registerEvent('composing', 'onComposing', 'chat');
        $this->registerEvent('paused', 'onPaused', 'chat');
        $this->registerEvent('gone', 'onGone', 'chat');
        $this->registerEvent('subject', 'onConferenceSubject', 'chat');
        $this->registerEvent('muc_setsubject_handle', 'onConferenceSubject', 'chat');

        $this->registerEvent('muc_getconfig_handle', 'onRoomConfig', 'chat');
        $this->registerEvent('muc_setconfig_handle', 'onRoomConfigSaved', 'chat');
        $this->registerEvent('muc_setconfig_error', 'onRoomConfigError', 'chat');
        $this->registerEvent('presence_muc_handle', 'onMucConnected', 'chat');

        $this->registerEvent('bob_request_handle', 'onSticker');
        //$this->registerEvent('presence', 'onPresence');
    }

    /*
     * Disabled for the moment, it SPAM a bit too much the user
    function onPresence($packet)
    {
        $contacts = $packet->content;
        if ($contacts != null){
            $contact = $contacts[0];

            if ($contact->value < 5) {
                $avatar = $contact->getPhoto();
                if ($avatar == false) $avatar = null;

                $presences = getPresences();
                $presence = $presences[$contact->value];

                Notification::append('presence', $contact->truename, $presence, $avatar, 4);
            }
        }
    }*/

    function onMessageHistory($packet)
    {
        $this->onMessage($packet, true);
    }

    function onMessageReceipt($packet)
    {
        $this->onMessage($packet, false, true);
    }

    function onMessage($packet, $history = false, $receipt = false)
    {
        $message = $packet->content;

        if ($message->isEmpty()) return;

        if ($message->user_id == $message->jidto && !$history
        && $message->jidfrom != $message->jidto) {
            $from = $message->jidfrom;
            $roster = $this->user->session->contacts()->where('jid', $from)->first();
            $contact = App\Contact::firstOrNew(['id' => $from]);

            if ($contact != null
            //&& $message->isTrusted()
            && !$message->isOTR()
            && $message->type != 'groupchat'
            && !$message->edited) {
                Notification::append(
                    'chat|'.$from,
                    $roster ? $roster->truename : $contact->truename,
                    $message->body,
                    $contact->getPhoto(),
                    4,
                    $this->route('chat', $contact->jid)
                );
            } elseif ($message->type == 'groupchat'
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
                    4);
            }

            $this->rpc('MovimTpl.fill', '#' . cleanupId($from.'_state'), $contact->jid);
        } else {
            // If the message is from me we reset the notif counter
            $from = $message->jidto;
            $n = new Notification;
            $n->ajaxClear('chat|'.$from);
        }

        if (!$message->isOTR()) {
            $this->rpc('Chat.appendMessagesWrapper', $this->prepareMessage($message, $from));
        }
    }

    function onSticker($packet)
    {
        list($to, $cid) = array_values($packet->content);
        $this->ajaxGet($to);
    }

    function onComposing($array)
    {
        $this->setState($array, $this->__('message.composing'));
    }

    function onPaused($array)
    {
        $this->setState($array, $this->__('message.paused'));
    }

    function onGone($array)
    {
        $this->setState($array, $this->__('message.gone'));
    }

    function onConferenceSubject($packet)
    {
        $this->ajaxGetRoom($packet->content->jidfrom);
    }

    function onMAMRetrieved($packet)
    {
        $this->ajaxGetRoom($packet->content);
    }

    function onMucConnected($packet)
    {
        $this->ajaxGetRoom($packet->content->jid);
    }

    function onRoomConfigError($packet)
    {
        Notification::append(false, $packet->content);
    }

    function onRoomConfig($packet)
    {
        list($config, $room) = array_values($packet->content);

        $view = $this->tpl();

        $xml = new \XMPPtoForm;
        $form = $xml->getHTML($config->x->asXML());

        $view->assign('form', $form);
        $view->assign('room', $room);

        Dialog::fill($view->draw('_chat_config_room'), true);
    }

    function onRoomConfigSaved($packet)
    {
        Notification::append(false, $this->__('chatroom.config_saved'));
    }

    private function setState($array, $message)
    {
        list($from, $to) = $array;

        $jid = ($from == $this->user->id) ? $to : $from;

        $view = $this->tpl();
        $view->assign('message', $message);

        $html = $view->draw('_chat_state');

        $this->rpc('MovimTpl.fill', '#' . cleanupId($jid.'_state'), $html);
    }

    /**
     * @brief Get a discussion
     * @param string $jid
     */
    function ajaxGet($jid = null)
    {
        if ($jid == null) {
            $this->rpc('Notification.current', 'chat');
            $this->rpc('MovimUtils.pushState', $this->route('chat'));
            $this->rpc('MovimTpl.fill', '#chat_widget', $this->prepareEmpty());
        } else {
            $notif = new Notification;
            $notif->ajaxClear('chat|'.$jid);
            $this->rpc('Notification.current', 'chat|'.$jid);

            $html = $this->prepareChat($jid);

            $this->rpc('MovimUtils.pushState', $this->route('chat', $jid));

            $this->rpc('MovimTpl.fill', '#chat_widget', $html);
            $this->rpc('MovimTpl.showPanel');
            $this->rpc('Chat.focus');

            $this->prepareMessages($jid);
        }
    }

    /**
     * @brief Get a chatroom
     * @param string $jid
     */
    function ajaxGetRoom($room)
    {
        if (!$this->validateJid($room)) return;

        $r = $this->user->session->conferences()->where('conference', $room)->first();

        if ($r) {
            if (!$r->connected) {
                $this->rpc('Rooms_ajaxJoin', $r->conference, $r->nick);
            }

            $html = $this->prepareChat($room, true);

            $this->rpc('MovimUtils.pushState', $this->route('chat', [$room, 'room']));

            $this->rpc('MovimTpl.fill', '#chat_widget', $html);
            $this->rpc('MovimTpl.showPanel');
            $this->rpc('Chat.focus');

            $this->prepareMessages($room, true);

            $notif = new Notification;
            $notif->ajaxClear('chat|'.$room);
            $this->rpc('Notification.current', 'chat|'.$room);
        } else {
            $this->rpc('Rooms_ajaxAdd', $room);
        }
    }

    /**
     * @brief Get a Drawer view of a contact
     */
    function ajaxGetContact($jid)
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
    function ajaxHttpSendMessage($to, $message = false, $muc = false, $resource = false, $replace = false, $file = false)
    {
        $message = trim($message);

        if (filter_var($message, FILTER_VALIDATE_URL)) {
            $headers = requestHeaders($message);

            if ($headers['http_code'] == 200
            && typeIsPicture($headers['content_type'])
            && $headers['download_content_length'] > 100) {
                $file = new \stdClass;
                $file->name = $message;
                $file->type = $headers['content_type'];
                $file->size = $headers['download_content_length'];
                $file->uri  = $message;
            }
        }

        $body = ($file != false)
            ? $file->uri
            : (string)htmlentities($message, ENT_XML1, 'UTF-8');

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
                'edited' => true
            ]);
        } else {
            $m = new \App\Message;
            $m->id          = generateUUID();
            $m->user_id     = $this->user->id;
            $m->jidto       = echapJid($to);
            $m->jidfrom     = $this->user->id;
            $m->published   = gmdate('Y-m-d H:i:s');
        }

        // TODO: make this boolean configurable
        $m->markable = true;

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

        $p->request();

        /* Is it really clean ? */
        if (!$p->getMuc()) {
            $m->save();
            $m->oldid = $oldid;

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
    function ajaxHttpCorrect($to, $message)
    {
        $m = $this->user->messages()
                        ->where('jidto', $to)
                        ->orderBy('published', 'desc')
                        ->first();

        if ($m) {
            $this->ajaxHttpSendMessage($to, $message, false, false, $m);
        }
    }

    /**
     * @brief Get the last message sent
     *
     * @param string $to
     * @return void
     */
    function ajaxLast($to)
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
    function ajaxSendComposing($to)
    {
        if (!$this->validateJid($to)) return;

        $mc = new Composing;
        $mc->setTo($to)->request();
    }

    /**
     * @brief Send a "paused" message
     *
     * @param string $to
     * @return void
     */
    function ajaxSendPaused($to)
    {
        if (!$this->validateJid($to)) return;

        $mp = new Paused;
        $mp->setTo($to)->request();
    }

    /**
     * @brief Get the chat history
     *
     * @param string jid
     * @param string time
     */
    function ajaxGetHistory($jid, $date, $muc = false)
    {
        if (!$this->validateJid($jid)) return;

        $messages = $this->user->messages()
                         ->where(function ($query) use ($jid) {
                                $query->where('jidfrom', $jid)
                                      ->orWhere('jidto', $jid);
                         })
                         ->where('published', '<', date(SQL_DATE, strtotime($date)));


        $messages = $muc
            ? $messages->where('type', 'groupchat')->whereNull('subject')
            : $messages->whereIn('type', ['chat', 'headline', 'invitation']);

        $messages = $messages->orderBy('published', 'desc')
                             ->take($this->_pagination)
                             ->get();

        if ($messages->count() > 0) {
            Notification::append(false, $this->__('message.history', $messages->count()));

            foreach($messages as $message) {
                if (!$message->isOTR()) {
                    $this->prepareMessage($message);
                }
            }

            $this->rpc('Chat.appendMessagesWrapper', $this->_wrapper, true);
            $this->_wrapper = [];
        }
    }

    /**
     * @brief Configure a room
     *
     * @param string $room
     */
    function ajaxGetRoomConfig($room)
    {
        if (!$this->validateJid($room)) return;

        $gc = new GetConfig;
        $gc->setTo($room)
           ->request();
    }

    /**
     * @brief Save the room configuration
     *
     * @param string $room
     */
    function ajaxSetRoomConfig($data, $room)
    {
        if (!$this->validateJid($room)) return;

        $sc = new SetConfig;
        $sc->setTo($room)
           ->setData($data)
           ->request();
    }

    /**
     * @brief Set last displayed message
     */
    function ajaxDisplayed($jid, $id)
    {
        if (!$this->validateJid($jid)) return;

        $message = $this->user->messages()->where('id', $id)->first();

        if ($message
        && $message->markable == true
        && $message->displayed == null) {
            $message->displayed = gmdate('Y-m-d H:i:s');
            $message->save();

            \Moxl\Stanza\Message::displayed($jid, $id);
        }
    }

    /**
     * @brief Clear the history
     *
     * @param string $room
     */
    function ajaxClearHistory($jid)
    {
        if (!$this->validateJid($jid)) return;

        $this->user->messages()->where(function ($query) use ($jid) {
            $query->where('jidfrom', $jid)
                  ->orWhere('jidto', $jid);
        })->delete();

        $this->ajaxGet($jid);
    }

    function prepareChat($jid, $muc = false)
    {
        $view = $this->tpl();

        $view->assign('jid', $jid);

        $jid = echapJS($jid);

        $view->assign('smiley', $this->call('ajaxSmiley'));
        $view->assign('emoji', prepareString('😀'));
        $view->assign('muc', $muc);
        $view->assign('anon', false);
        $view->assign('info',
            \App\Info::where('server', $this->user->session->host)
                     ->where('node', '')
                     ->first());

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

    function prepareMessages($jid, $muc = false)
    {
        if (!$this->validateJid($jid)) return;

        $jid = echapJid($jid);

        $messages = $this->user->messages()->where(function ($query) use ($jid) {
            $query->where('jidfrom', $jid)
                  ->orWhere('jidto', $jid);
        });

        $messages = $muc
            ? $messages->where('type', 'groupchat')->whereNull('subject')
            : $messages->whereIn('type', ['chat', 'headline', 'invitation']);

        $messages = $messages->orderBy('published', 'desc')->take($this->_pagination)->get();
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

        $date = $view->draw('_chat_date');

        $separator = $view->draw('_chat_separator');

        $this->rpc('Chat.setBubbles', $left, $right, $date, $separator);
        $this->rpc('Chat.appendMessagesWrapper', $this->_wrapper);

        $notif = new Notification;
        $this->rpc('Chat.insertSeparator', $notif->getCounter('chat|'.$jid));
        $notif->ajaxClear('chat|'.$jid);

        $this->rpc('MovimTpl.scrollPanel');
    }

    function prepareMessage(&$message, $jid = null)
    {
        if ($jid != $message->jidto && $jid != $message->jidfrom && $jid != null) {
            return $this->_wrapper;
        }

        $message->jidto = echapJS($message->jidto);
        $message->jidfrom = echapJS($message->jidfrom);

        // Attached file
        if (isset($message->file)) {
            if ($message->body == $message->file['uri']) {
                $message->body = null;
            }

            // We proxify pictures links even if they are advertized as small ones
            if (typeIsPicture($message->file['type'])
            && $message->file['size'] <= SMALL_PICTURE_LIMIT) {
                $message->thumb   = $this->route('picture', urlencode($message->file['uri']));
                $message->picture = $message->file['uri'];
            }

            if (typeIsAudio($message->file['type'])
            && $message->file['size'] <= SMALL_PICTURE_LIMIT) {
                $message->audio = $message->file['uri'];
            }
        }

        if (isset($message->html)) {
            $message->body = $message->html;
        } else {
            $message->convertEmojis();
            $message->addUrls();
        }

        if (isset($message->subject) && $message->type == 'headline') {
            $message->body = $message->subject.': '.$message->body;
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

        if (isset($message->picture)) {
            $message->sticker = [
                'thumb' => $message->thumb,
                'url' => $message->picture,
                'picture' => true
            ];
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

        if (empty($date)) $date = $this->__('date.today');

        // We create the date wrapper
        if (!array_key_exists($date, $this->_wrapper)) {
            $this->_wrapper[$date] = [];
        }

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
                $message->mine = ($this->mucPresences[$key]->mucjid == $this->user->id);
            } else {
                $this->mucPresences[$key] = true;
            }

            $message->icon = firstLetterCapitalize($message->resource);
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

        return $this->_wrapper;
    }

    function prepareEmpty()
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
        if ($chats == null) $chats = [];
        $chats[$this->user->id] = true;

        $top = $this->user->session->contacts()->join(DB::raw('(
            select jidfrom as id, count(*) as number
            from messages
            where published >= \''.date('Y-m-d', strtotime('-4 week')).'\'
            group by jidfrom) as top
            '), 'top.id', '=', 'rosters.jid')
            ->join(DB::raw('(
            select min(value) as value, jid
            from presences
            group by jid) as presences
            '), 'presences.jid', '=', 'rosters.jid')
            ->whereNotIn('rosters.jid', array_keys($chats))
            ->orderBy('presences.value')
            ->orderBy('top.number', 'desc')
            ->with('presence.capability')
            ->take(8)
            ->get();

        $view->assign('conferences', $conferences);
        $view->assign('top', $top);

        return $view->draw('_chat_empty');
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

    function getSmileyPath($id)
    {
        return getSmileyPath($id);
    }

    function display()
    {
        $this->view->assign('pagination', $this->_pagination);
    }
}
