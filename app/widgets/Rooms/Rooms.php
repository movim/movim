<?php

use Moxl\Xec\Action\Presence\Muc;
use Moxl\Xec\Action\Bookmark\Get;
use Moxl\Xec\Action\Bookmark\Set;
use Moxl\Xec\Action\Presence\Unavailable;
use Moxl\Xec\Action\Message\Invite;
use Moxl\Xec\Action\Disco\Request;
use Moxl\Xec\Action\Muc\SetSubject;
use Moxl\Xec\Action\Vcard\Set as VcardSet;

use Respect\Validation\Validator;
use Illuminate\Support\Collection;

use Movim\Picture;
use Movim\Session;

class Rooms extends \Movim\Widget\Base
{
    function load()
    {
        $this->addjs('rooms.js');
        $this->addcss('rooms.css');
        $this->registerEvent('message', 'onMessage');
        $this->registerEvent('bookmark_get_handle', 'onGetBookmark');
        $this->registerEvent('bookmark_set_handle', 'onBookmark');
        $this->registerEvent('vcard_set_handle', 'onAvatarSet', 'chat');
        $this->registerEvent('presence_muc_handle', 'onConnected', 'chat');
        $this->registerEvent('presence_unavailable_handle', 'onDisconnected', 'chat');
        $this->registerEvent('presence_muc_errorconflict', 'onConflict');
        $this->registerEvent('presence_muc_errorregistrationrequired', 'onRegistrationRequired');
        $this->registerEvent('presence_muc_errorremoteservernotfound', 'onRemoteServerNotFound');
    }

    function onMessage($packet)
    {
        $message = $packet->content;

        if ($message->user_id == $message->jidto
        && $message->type == 'groupchat'
        && $message->subject == null) {
            Notification::append(
                'chat|'.$message->jidfrom,
                null,
                $message->body,
                null,
                0,
                null
            );
        }
    }

    function onAvatarSet($packet)
    {
        $this->rpc('Dialog_ajaxClear');
        Notification::append(null, $this->__('avatar.updated'));
    }

    function onRegistrationRequired($packet)
    {
        Notification::append(null, $this->__('chatrooms.registrationrequired'));
        $this->ajaxExit($packet->content);
    }

    function onRemoteServerNotFound($packet)
    {
        Notification::append(null, $this->__('chatrooms.remoteservernotfound'));
        $this->ajaxExit($packet->content);
    }

    function onGetBookmark()
    {
        foreach($this->user->session->conferences as $room) {
            if ($room->autojoin && !$room->connected) {
                $this->ajaxJoin($room->conference, $room->nick);
            }
        }

        $this->refreshRooms();
    }

    function onBookmark()
    {
        $this->refreshRooms();
        $this->rpc('MovimTpl.hidePanel');
    }

    function onConnected($packet)
    {
        $this->refreshRooms();
    }

    function onConflict()
    {
        Notification::append(null, $this->__('chatrooms.conflict'));
    }

    function onDisconnected()
    {
        Notification::append(null, $this->__('chatrooms.disconnected'));
    }

    private function refreshRooms($edit = false)
    {
        $this->rpc('MovimTpl.fill', '#rooms_widget', $this->prepareRooms($edit));
        $this->rpc('Rooms.refresh');
    }

    /**
     * @brief Get the Rooms
     */
    public function ajaxDisplay($edit = false)
    {
        $this->refreshRooms($edit);
    }

    /**
     * @brief Display the add room form
     */
    function ajaxAdd($room = false)
    {
        $view = $this->tpl();

        $view->assign('info', \App\Info::where('server', $room)
                                       ->where('category', 'conference')
                                       ->first());
        $view->assign('id', $room);
        $view->assign('conference',
            $this->user->session->conferences()
            ->where('conference', $room)->first());
        $view->assign('username', $this->user->session->username);

        $this->rpc('Rooms.setDefaultServices', $this->user->session->getChatroomsServices());

        Dialog::fill($view->draw('_rooms_add'));
    }

    /**
     * @brief Get the avatar form
     */
    function ajaxGetAvatar($room)
    {
        if (!$this->validateRoom($room)) return;

        $view = $this->tpl();
        $view->assign('room', $this->user->session->conferences()
                                 ->where('conference', $room)
                                 ->first());

        Dialog::fill($view->draw('_rooms_avatar'));
    }

    /**
     * @brief Set the avatar
     */
    function ajaxSetAvatar($room, $form)
    {
        if (!$this->validateRoom($room)) return;

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
    function ajaxGetSubject($room)
    {
        if (!$this->validateRoom($room)) return;

        $view = $this->tpl();
        $view->assign('room', $this->user->session->conferences()
                                 ->where('conference', $room)
                                 ->first());

        Dialog::fill($view->draw('_rooms_subject'));
    }

    /**
     * @brief Change the subject of a chatroom
     */
    function ajaxSetSubject($room, $form)
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
    function ajaxAskInvite($room = false)
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
    function ajaxInvite($form)
    {
        if (!$this->validateRoom($form->to->value)) return;

        if (!empty($form->invite->value)) {
            $i = new Invite;
            $i->setTo($form->to->value)
              ->setId(generateUUID())
              ->setInvite($form->invite->value)
              ->request();

            Notification::append(null, $this->__('room.invited'));
            $this->rpc('Dialog_ajaxClear');
        }
    }

    /**
     * @brief Display the remove room confirmation
     */
    function ajaxRemoveConfirm($room)
    {
        if (!$this->validateRoom($room)) return;

        $view = $this->tpl();

        $view->assign('room', $room);

        Dialog::fill($view->draw('_rooms_remove'));
    }

    /**
     * @brief Display the room list
     */
    function ajaxList($room)
    {
        if (!$this->validateRoom($room)) return;

        $view = $this->tpl();
        $view->assign('list', $this->user->session->conferences()
                                   ->where('conference', $room)
                                   ->first()
                                   ->presences()
                                   ->with('capability')
                                   ->get());
        $view->assign('room', $room);
        $view->assign('me', $this->user->id);

        Dialog::fill($view->draw('_rooms_list'), true);
    }

    /**
     * @brief Autocomplete users in MUC
     */
    function ajaxMucUsersAutocomplete($room)
    {
        $this->rpc("Chat.onAutocomplete", $this->user->session->conferences()
                                               ->where('conference', $room)
                                               ->first()->presences
                                               ->pluck('resource'));
    }

    /**
     * @brief Remove a room
     */
    function ajaxRemove($room)
    {
        if (!$this->validateRoom($room)) return;

        $this->user->session->conferences()->where('conference', $room)->delete();

        $this->setBookmark();
    }

    /**
     * @brief Join a chatroom
     */
    function ajaxJoin($room, $nickname = false)
    {
        if (!$this->validateRoom($room)) return;

        $r = new Request;
        $r->setTo($room)
          ->request();

        $p = new Muc;
        $p->setTo($room);

        /*$c = new \Moxl\Xec\Action\Disco\Request;
        $c->setTo(explodeJid($room)['server'])
          ->request();*/

        if ($nickname == false) {
            $nickname = $this->user->session->username;
        }

        $jid = explodeJid($room);
        $capability = App\Capability::find($jid['server']);

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
    function ajaxExit($room)
    {
        if (!$this->validateRoom($room)) return;

        // We reset the Chat view
        $c = new Chat;
        $c->ajaxGet();

        // We properly exit
        $resource = $this->user->session->conferences()
            ->where('conference', $room)
            ->first()->presences()
            ->where('mucjid', $this->user->id)
            ->first()
            ->resource;

        $jid = explodeJid($room);
        $capability = App\Capability::find($jid['server']);

        if (!$capability || !$capability->isMAM()) {
            $this->user->messages()->where('jidfrom', $room)->delete();
        }

        $this->user->session->conferences()
             ->where('conference', $room)
             ->first()->presences()->delete();

        $this->refreshRooms();

        $session = Session::start();
        $session->remove($room . '/' .$resource);

        $pu = new Unavailable;
        $pu->setTo($room)
           ->setResource($resource)
           ->request();
    }

    /**
     * @brief Confirm the room add
     */
    function ajaxChatroomAdd($form)
    {
        if (!filter_var($form->jid->value, FILTER_VALIDATE_EMAIL)) {
            Notification::append(null, $this->__('chatrooms.bad_id'));
        } elseif (trim($form->name->value) == '') {
            Notification::append(null, $this->__('chatrooms.empty_name'));
        } else {
            $this->user->session->conferences()
                 ->where('conference', strtolower($form->jid->value))
                 ->delete();

            $item = [
                    'type'      => 'conference',
                    'name'      => $form->name->value,
                    'autojoin'  => $form->autojoin->value,
                    'nick'      => $form->nick->value,
                    'jid'       => strtolower($form->jid->value)
                    ];
            $this->setBookmark($item);
            $this->rpc('Dialog_ajaxClear');
        }
    }

    public function setBookmark($item = false)
    {
        $arr = [];

        if ($item) {
            array_push($arr, $item);
        }

        $conferences = $this->user->session->conferences;
        if ($conferences) {
            foreach ($conferences as $c) {
                array_push($arr,
                    [
                        'type'      => 'conference',
                        'name'      => $c->name,
                        'autojoin'  => $c->autojoin,
                        'nick'      => $c->nick,
                        'jid'       => $c->conference
                    ]
                );
            }
        }

        $b = new Set;
        $b->setArr($arr)
          ->request();
    }

    function prepareRooms($edit = false)
    {
        if (!$this->user->session) return '';

        $conferences = $this->user->session->conferences()
                                           ->with('info', 'contact', 'presence')
                                           ->get();
        $connected = new Collection;

        foreach ($conferences as $key => $conference) {
            if ($conference->connected) {
                $connected->push($conferences->pull($key));
            }
        }

        $conferences = $connected->merge($conferences);

        $view = $this->tpl();
        $view->assign('edit', $edit);
        $view->assign('conferences', $conferences);
        $view->assign('room', $this->get('r'));

        return $view->draw('_rooms');
    }

    /**
     * @brief Validate the room
     *
     * @param string $room
     */
    private function validateRoom($room)
    {
        return (Validator::stringType()->noWhitespace()->length(6, 80)->validate($room));
    }

    /**
     * @brief Validate the resource
     *
     * @param string $resource
     */
    private function validateResource($resource)
    {
        return (Validator::stringType()->length(2, 40)->validate($resource));
    }
}
