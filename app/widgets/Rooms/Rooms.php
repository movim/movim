<?php

use Moxl\Xec\Action\Presence\Muc;
use Moxl\Xec\Action\Bookmark2\Set;
use Moxl\Xec\Action\Bookmark2\Delete;
use Moxl\Xec\Action\Presence\Unavailable;
use Moxl\Xec\Action\Message\Invite;
use Moxl\Xec\Action\Disco\Request;
use Moxl\Xec\Action\Disco\Items;
use Moxl\Xec\Action\Muc\SetSubject;
use Moxl\Xec\Action\Muc\Destroy;
use Moxl\Xec\Action\Vcard\Set as VcardSet;

use Respect\Validation\Validator;
use Illuminate\Support\Collection;

use Movim\Widget\Base;
use Movim\Picture;
use Movim\Session;
use Movim\ChatStates;

use App\Conference;

class Rooms extends Base
{
    public function load()
    {
        $this->addjs('rooms.js');
        $this->addcss('rooms.css');
        $this->registerEvent('message', 'onMessage');
        $this->registerEvent('bookmark2_get_handle', 'onBookmarkGet');
        $this->registerEvent('bookmark2', 'onBookmarkSet');
        $this->registerEvent('bookmark2_set_handle', 'onBookmarkSet');
        $this->registerEvent('bookmark2_delete_handle', 'onBookmarkSet');
        $this->registerEvent('bookmark_synchronize_handle', 'onBookmarkSynchronized');
        $this->registerEvent('disco_items_nosave_handle', 'onDiscoGateway');
        $this->registerEvent('disco_items_nosave_error', 'onDiscoGatewayError');
        $this->registerEvent('vcard_set_handle', 'onAvatarSet', 'chat');
        $this->registerEvent('muc_destroy_handle', 'onDestroyed', 'chat');
        $this->registerEvent('presence_muc_handle', 'onConnected', 'chat');
        $this->registerEvent('presence_unavailable_handle', 'onDisconnected', 'chat');
        $this->registerEvent('presence_muc_errorconflict', 'onConflict');
        $this->registerEvent('presence_muc_errorregistrationrequired', 'onRegistrationRequired');
        $this->registerEvent('presence_muc_errorremoteservernotfound', 'onRemoteServerNotFound');
        $this->registerEvent('composing', 'onComposing', 'chat');
        $this->registerEvent('paused', 'onPaused', 'chat');

        $this->registerEvent('chat_open_room', 'onChatOpen', 'chat');
    }

    public function onMessage($packet)
    {
        $message = $packet->content;

        $chatStates = ChatStates::getInstance();
        $chatStates->clearState($message->jidfrom, $message->resource);
        $this->onPaused($chatStates->getState($message->jidfrom));

        $this->setCounter($message->jidfrom);
    }

    public function onChatOpen(string $room)
    {
        $this->setCounter($room);
    }

    public function onDiscoGateway($packet)
    {
        $view = $this->tpl();
        $view->assign('rooms', $packet->content);
        $this->rpc('MovimTpl.fill', '#gateway_rooms', $view->draw('_rooms_gateway_rooms'));
    }

    public function onDiscoGatewayError($packet)
    {
        $this->ajaxResetGatewayRooms();
    }

    public function onComposing(array $array)
    {
        $this->setState($array[0], true);
    }

    public function onPaused(array $array)
    {
        $this->setState($array[0], false);
    }

    public function onAvatarSet($packet)
    {
        $this->rpc('Dialog_ajaxClear');
        Notification::toast($this->__('avatar.updated'));
    }

    public function onRegistrationRequired($packet)
    {
        Notification::toast($this->__('chatrooms.registrationrequired'));
        $this->ajaxExit($packet->content);
    }

    public function onRemoteServerNotFound($packet)
    {
        Notification::toast($this->__('chatrooms.remoteservernotfound'));
        $this->ajaxExit($packet->content);
    }

    public function onBookmarkGet()
    {
        foreach ($this->user->session->conferences as $room) {
            if ($room->autojoin && !$room->connected) {
                $this->ajaxJoin($room->conference, $room->nick);
            }
        }

        $this->refreshRooms();
    }

    public function onBookmarkSynchronized($packet)
    {
        Notification::toast($this->__('chatrooms.synchronized', $packet->content));
    }

    public function onDestroyed($packet)
    {
        $this->refreshRooms();
        $this->rpc('Chat_ajaxGet');

        Notification::toast($this->__('chatrooms.destroyed'));
    }

    public function onBookmarkSet($packet)
    {
        $conference = $packet->content;

        if ($conference && $conference->autojoin) {
            $this->ajaxJoin($conference->conference, $conference->nick);
        }

        Notification::toast($this->__('bookmarks.updated'));
        $this->refreshRooms();
    }

    public function onConnected($packet)
    {
        $this->refreshRooms();
    }

    public function onConflict()
    {
        Notification::toast($this->__('chatrooms.conflict'));
    }

    public function onDisconnected()
    {
        $this->refreshRooms();
        Notification::toast($this->__('chatrooms.disconnected'));
    }

    private function setState(string $room, bool $composing)
    {
        $this->rpc(
            $composing
                ? 'MovimUtils.addClass'
                : 'MovimUtils.removeClass',
            '#' . cleanupId($room.'_rooms_primary'),
            'composing'
        );
    }

    private function setCounter(string $room)
    {
        $conference = $this->user->session
                           ->conferences()
                           ->where('conference', $room)
                           ->withCount('unreads')
                           ->first();

        if ($conference) {
            $this->rpc(
                'MovimTpl.fill',
                '#' . cleanupId($room.'_rooms_primary'),
                $this->prepareRoomCounter($conference, $conference->getPhoto())
            );
        }
    }

    private function refreshRooms($edit = false, $all = false)
    {
        $this->rpc('MovimTpl.fill', '#rooms_widget', $this->prepareRooms($edit, $all));
        $this->rpc('Rooms.refresh');
    }

    public function ajaxResetGatewayRooms()
    {
        $this->rpc('MovimTpl.fill', '#gateway_rooms', '');
    }

    /**
     * @brief Get the Rooms
     */
    public function ajaxDisplay($edit = false, $all = false)
    {
        $this->refreshRooms($edit, $all);
    }

    /**
     * @brief Display the add room form
     */
    public function ajaxAdd($room = false)
    {
        $view = $this->tpl();

        $view->assign('info', \App\Info::where('server', $room)
                                       ->where('node', '')
                                       ->whereCategory('conference')
                                       ->first());
        $view->assign('mucservice', \App\Info::where('server', 'like', '%'. $this->user->session->host)
                                             ->where('server', 'not like', '%@%')
                                             ->whereCategory('conference')
                                             ->whereType('text')
                                             ->first());
        $view->assign('id', $room);
        $view->assign(
            'conference',
            $this->user->session->conferences()
            ->where('conference', $room)->first()
        );
        $view->assign('username', $this->user->session->username);
        $view->assign(
            'gateways',
            \App\Info::whereIn('server', function ($query) {
                $query->select('jid')->from('presences');
            })
            ->whereCategory('gateway')
            ->get()
        );

        $this->rpc('Rooms.setDefaultServices', $this->user->session->getChatroomsServices());

        Dialog::fill($view->draw('_rooms_add'));
    }

    /**
     * Display the room subject
     */
    public function ajaxShowSubject($room = false)
    {
        if (!$this->validateRoom($room)) {
            return;
        }

        $conference = $this->user->session->conferences()
            ->where('conference', $room)
            ->with('info')
            ->first();

        if (!$conference) return;

        $view = $this->tpl();
        $view->assign('conference', $conference);
        $view->assign('room', $room);

        Dialog::fill($view->draw('_rooms_subject_show'));
    }

    /**
     * @brief Discover rooms for a gateway
     */
    public function ajaxDiscoGateway(string $server)
    {
        if (empty($server)) {
            $this->ajaxResetGatewayRooms();
            $this->rpc('Rooms.selectGatewayRoom', '', '');
        } else {
            $r = new Items;
            $r->setTo($server)
              ->disableSave()
              ->request();
        }
    }

    /**
     * @brief Get the avatar form
     */
    public function ajaxGetAvatar($room)
    {
        if (!$this->validateRoom($room)) {
            return;
        }

        $view = $this->tpl();
        $view->assign('room', $this->user->session->conferences()
                                 ->where('conference', $room)
                                 ->first());

        Dialog::fill($view->draw('_rooms_avatar'));
    }

    /**
     * @brief Set the avatar
     */
    public function ajaxSetAvatar($room, $form)
    {
        if (!$this->validateRoom($room)) {
            return;
        }

        $p = new Picture;
        $p->fromBase($form->photobin->value);

        $p->set('temp', 'jpeg', 60);

        $p = new Picture;
        $p->get('temp');

        $vcard = new stdClass;
        $vcard->photobin = new stdClass;
        $vcard->phototype = new stdClass;
        $vcard->photobin->value = $p->toBase();
        $vcard->phototype->value = 'image/jpeg';

        $r = new VcardSet;
        $r->setData($vcard)->setTo($room)->request();
    }

    /**
     * @brief Get the subject form of a chatroom
     */
    public function ajaxGetSubject($room)
    {
        if (!$this->validateRoom($room)) {
            return;
        }

        $view = $this->tpl();
        $view->assign('room', $this->user->session->conferences()
                                 ->where('conference', $room)
                                 ->first());

        Dialog::fill($view->draw('_rooms_subject'));
        $this->rpc('MovimUtils.applyAutoheight');
    }

    /**
     * @brief Change the subject of a chatroom
     */
    public function ajaxSetSubject($room, $form)
    {
        if (!$this->validateRoom($room)
        || !Validator::stringType()->length(0, 200)->validate($form->subject->value)) {
            return;
        }

        $p = new SetSubject;
        $p->setTo($room)
          ->setSubject($form->subject->value)
          ->request();
    }


    /**
     * @brief Display the add room form
     */
    public function ajaxAskInvite($room = false)
    {
        $view = $this->tpl();

        $view->assign('contacts', $this->user->session->contacts()->pluck('jid'));
        $view->assign('room', $room);
        $view->assign('invite', \App\Invite::set($this->user->id, $room));

        Dialog::fill($view->draw('_rooms_invite'));
    }


    /**
     * @brief Invite someone to a room
     */
    public function ajaxInvite($form)
    {
        if (!$this->validateRoom($form->to->value)) {
            return;
        }

        if (!empty($form->invite->value)) {
            $i = new Invite;
            $i->setTo($form->to->value)
              ->setId(generateUUID())
              ->setInvite($form->invite->value)
              ->request();

            Notification::toast($this->__('room.invited'));
            $this->rpc('Dialog_ajaxClear');
        }
    }

    /**
     * @brief Display the remove room confirmation
     */
    public function ajaxRemoveConfirm($room)
    {
        if (!$this->validateRoom($room)) {
            return;
        }

        $view = $this->tpl();

        $view->assign('room', $room);

        Dialog::fill($view->draw('_rooms_remove'));
    }

    /**
     * @brief Display the room list
     */
    public function ajaxList($room)
    {
        if (!$this->validateRoom($room)) {
            return;
        }

        $conference = $this->user->session->conferences()
            ->where('conference', $room)
            ->first();

        if (!$conference) return;

        $view = $this->tpl();
        $view->assign('list', $conference->presences()
            ->with('capability')
            ->get());
        $view->assign('conference', $conference);
        $view->assign('room', $room);
        $view->assign('me', $this->user->id);

        Dialog::fill($view->draw('_rooms_list'), true);
    }

    /**
     * @brief Autocomplete users in MUC
     */
    public function ajaxMucUsersAutocomplete($room)
    {
        $this->rpc("Chat.onAutocomplete", $this->user->session->conferences()
                                               ->where('conference', $room)
                                               ->first()->presences
                                               ->pluck('resource'));
    }

    /**
     * @brief Remove a room
     */
    public function ajaxRemove($room)
    {
        if (!$this->validateRoom($room)) {
            return;
        }

        $d = new Delete;
        $d->setId($room)
          ->request();
    }

    /**
     * @brief Destroy a room
     */
    public function ajaxAskDestroy($room)
    {
        if (!$this->validateRoom($room)) {
            return;
        }

        $view = $this->tpl();
        $view->assign('room', $room);

        Dialog::fill($view->draw('_rooms_destroy'));
    }
    /**
     * @brief Destroy a room
     */
    public function ajaxDestroy($room)
    {
        if (!$this->validateRoom($room)) {
            return;
        }

        $d = new Destroy;
        $d->setTo($room)
          ->request();
    }

    /**
     * @brief Join a chatroom
     */
    public function ajaxJoin($room, $nickname = false)
    {
        if (!$this->validateRoom($room)) {
            return;
        }

        $r = new Request;
        $r->setTo($room)
          ->request();

        $p = new Muc;
        $p->setTo($room);

        if ($nickname == false) {
            $nickname = $this->user->session->username;
        }

        $jid = explodeJid($room);
        $capability = \App\Info::where('server', $jid['server'])
                               ->where('node', '')
                               ->first();

        if ($capability && ($capability->isMAM() || $capability->isMAM2())) {
            $p->enableMAM();

            if ($capability->isMAM2()) {
                $p->enableMAM2();
            }
        } else {
            $r = new Request;
            $r->setTo($jid['server'])
              ->request();
        }

        $p->setNickname($nickname);
        $p->request();
    }

    /**
     * @brief Exit a room
     *
     * @param string $room
     */
    public function ajaxExit($room)
    {
        if (!$this->validateRoom($room)) {
            return;
        }

        // We reset the Chat view
        $this->rpc('Chat_ajaxGet');

        // We properly exit
        $resource = $this->user->session->conferences()
            ->where('conference', $room)
            ->first();

        if (!$resource) return;

        $resource = $resource
            ->presences()
            ->where('mucjid', $this->user->id)
            ->first();

        $resource = $resource
            ? $resource->resource
            : null;

        $jid = explodeJid($room);
        $capability = \App\Info::where('server', $jid['server'])
                               ->where('node', '')
                               ->first();

        if (!$capability || !$capability->isMAM()) {
            $this->user->messages()->where('jidfrom', $room)->delete();
        }

        $this->user->session->conferences()
             ->where('conference', $room)
             ->first()->presences()->delete();

        $this->refreshRooms();

        if ($resource) {
            $session = Session::start();
            $session->remove($room . '/' .$resource);

            $pu = new Unavailable;
            $pu->setTo($room)
               ->setResource($resource)
               ->request();
        }
    }

    /**
     * @brief Confirm the room add
     */
    public function ajaxChatroomAdd($form)
    {
        if (!$this->validateRoom($form->jid->value)) {
            Notification::toast($this->__('chatrooms.bad_id'));
        } elseif (trim($form->name->value) == '') {
            Notification::toast($this->__('chatrooms.empty_name'));
        } else {
            $this->ajaxExit($form->jid->value);

            $this->user->session->conferences()
                 ->where('conference', strtolower($form->jid->value))
                 ->delete();

            $conference = new Conference;
            $conference->conference = strtolower($form->jid->value);
            $conference->name = $form->name->value;
            $conference->autojoin = $form->autojoin->value;
            $conference->nick = $form->nick->value;

            $b = new Set;
            $b->setConference($conference)
              ->request();

            $this->rpc('Dialog_ajaxClear');
        }
    }

    /**
     * Synchronize Bookmark 1 to Bookmark 2
     */
    public function ajaxSyncBookmark()
    {
        $s = new \Moxl\Xec\Action\Bookmark\Synchronize;
        $s->request();
    }

    public function prepareRooms($edit = false, $all = false)
    {
        if (!$this->user->session) {
            return '';
        }

        $conferences = $this->user->session->conferences()
                                           ->with('info', 'contact', 'presence')
                                           ->withCount('unreads')
                                           ->get();
        $connected = new Collection;
        $disconnected = 0;
        $servers = [];

        foreach ($conferences as $key => $conference) {
            array_push($servers, $conference->server);
            if ($conference->connected) {
                $connected->push($conferences->pull($key));
            } else {
                $disconnected++;
            }
        }

        $conferences = $connected->merge($conferences);

        // If all the rooms are disconnected, we show everything
        if ($disconnected == $conferences->count()) {
            $all = true;
        }

        $view = $this->tpl();
        $view->assign('edit', $edit);
        $view->assign('all', $all);
        $view->assign('disconnected', $disconnected);
        $view->assign('servers', App\Info::with('identities')
                                         ->whereIn('server', array_unique($servers))
                                         ->get()
                                         ->keyBy('server')
        );
        $view->assign('conferences', $conferences);
        $view->assign('room', $this->get('r'));

        return $view->draw('_rooms');
    }

    public function prepareRoomCounter(Conference $conference, $withAvatar = false)
    {
        $view = $this->tpl();
        $view->assign('conference', $conference);
        $view->assign('withAvatar', $withAvatar);

        return $view->draw('_rooms_counter');
    }

    /**
     * @brief Validate the room
     *
     * @param string $room
     */
    private function validateRoom($room)
    {
        return (Validator::stringType()->noWhitespace()->contains('@')->length(6, 256)->validate($room));
    }
}
