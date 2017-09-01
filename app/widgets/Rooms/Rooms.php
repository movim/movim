<?php

use Moxl\Xec\Action\Presence\Muc;
use Moxl\Xec\Action\Bookmark\Get;
use Moxl\Xec\Action\Bookmark\Set;
use Moxl\Xec\Action\Presence\Unavailable;
use Moxl\Xec\Action\Message\Invite;
use Moxl\Xec\Action\Disco\Request;

use Ramsey\Uuid\Uuid;

use Respect\Validation\Validator;

use Movim\Session;

class Rooms extends \Movim\Widget\Base
{
    function load()
    {
        $this->addjs('rooms.js');
        $this->addcss('rooms.css');
        $this->registerEvent('message', 'onMessage');
        $this->registerEvent('bookmark_get_handle', 'onGetBookmark');
        $this->registerEvent('bookmark_set_handle', 'onBookmark', 'chat');
        $this->registerEvent('presence_muc_handle', 'onConnected', 'chat');
        $this->registerEvent('presence_unavailable_handle', 'onDisconnected', 'chat');
        $this->registerEvent('presence_muc_errorconflict', 'onConflict');
        $this->registerEvent('presence_muc_errorregistrationrequired', 'onRegistrationRequired');
        $this->registerEvent('presence_muc_errorremoteservernotfound', 'onRemoteServerNotFound');
    }

    function onMessage($packet)
    {
        $message = $packet->content;

        if($message->session == $message->jidto
        && $message->type == 'groupchat') {
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
        $cod = new \Modl\ConferenceDAO;
        $rooms = $cod->getAll();

        if(is_array($rooms)) {
            foreach($rooms as $room) {
                if ($room->autojoin
                && !$this->checkConnected($room->conference, $room->nick)) {
                    $this->ajaxJoin($room->conference, $room->nick);
                }
            }
        }
    }

    function onBookmark()
    {
        $this->refreshRooms();
        $this->rpc('MovimTpl.hidePanel');
    }

    function onConnected()
    {
        $this->refreshRooms();
    }

    function onConflict()
    {
        Notification::append(null, $this->__('chatrooms.conflict'));
    }

    function onDisconnected()
    {
        // We reset the Chat view
        $c = new Chat();
        $c->ajaxGet();

        $this->refreshRooms();
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

        $id = new \Modl\InfoDAO;
        $cd = new \Modl\ConferenceDAO;

        $view->assign('info', $id->getConference($room));
        $view->assign('id', $room);
        $view->assign('conference', $cd->get($room));
        $view->assign('username', $this->user->getUser());

        Dialog::fill($view->draw('_rooms_add', true));
    }

    /**
     * @brief Display the add room form
     */
    function ajaxAskInvite($room = false)
    {
        $view = $this->tpl();

        $cd = new \Modl\ContactDAO;
        $view->assign('contacts', $cd->getRosterSimple());
        $view->assign('room', $room);
        $view->assign('invite', \Modl\Invite::set($this->user->getLogin(), $room));

        Dialog::fill($view->draw('_rooms_invite', true));
    }


    /**
     * @brief Invite someone to a room
     */
    function ajaxInvite($form)
    {
        if(!$this->validateRoom($form->to->value)) return;

        if(!empty($form->invite->value)) {
            $i = new Invite;
            $i->setTo($form->to->value)
              ->setId(Uuid::uuid4())
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
        if(!$this->validateRoom($room)) return;

        $view = $this->tpl();

        $view->assign('room', $room);

        Dialog::fill($view->draw('_rooms_remove', true));
    }

    /**
     * @brief Display the room list
     */
    function ajaxList($room)
    {
        if(!$this->validateRoom($room)) return;

        $view = $this->tpl();

        $userslist = $this->getUsersList($room);
        $view->assign('list', $userslist);
        $view->assign('me', $this->user->getLogin());

        Dialog::fill($view->draw('_rooms_list', true), true);
    }

    /**
     * @brief Autocomplete users in MUC
     */
    function ajaxMucUsersAutocomplete($room)
    {
        $usersForAutocomplete = $this->getUsersList($room);

        if($usersForAutocomplete) {
            $this->rpc("Chat.onAutocomplete", $usersForAutocomplete);
        }
    }

    /**
     * @brief Remove a room
     */
    function ajaxRemove($room)
    {
        if(!$this->validateRoom($room)) return;

        $cd = new \Modl\ConferenceDAO;
        $cd->deleteNode($room);

        $this->setBookmark();
    }

    /**
     * @brief Join a chatroom
     */
    function ajaxJoin($room, $nickname = false)
    {
        if(!$this->validateRoom($room)) return;

        $r = new Request;
        $r->setTo($room)
          ->request();

        $p = new Muc;
        $p->setTo($room);

        if($nickname == false) {
            $s = Session::start();
            $nickname = $s->get('username');
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
        if(!$this->validateRoom($room)) return;

        // We reset the Chat view
        $c = new Chat();
        $c->ajaxGet();

        // We properly exit
        $s = Session::start();
        $resource = $s->get('username');

        $pu = new Unavailable;
        $pu->setTo($room)
           ->setResource($resource)
           ->setMuc()
           ->request();
    }

    /**
     * @brief Confirm the room add
     */
    function ajaxChatroomAdd($form)
    {
        if(!filter_var($form['jid'], FILTER_VALIDATE_EMAIL)) {
            Notification::append(null, $this->__('chatrooms.bad_id'));
        } elseif(trim($form['name']) == '') {
            Notification::append(null, $this->__('chatrooms.empty_name'));
        } else {
            $cd = new \Modl\ConferenceDAO;
            $cd->deleteNode($form['jid']);

            $item = [
                    'type'      => 'conference',
                    'name'      => $form['name'],
                    'autojoin'  => $form['autojoin'],
                    'nick'      => $form['nick'],
                    'jid'       => strtolower($form['jid'])
                    ];
            $this->setBookmark($item);
            $this->rpc('Dialog_ajaxClear');
        }
    }

    public function setBookmark($item = false)
    {
        $arr = [];

        if($item) {
            array_push($arr, $item);
        }

        $sd = new \Modl\SubscriptionDAO;
        $cd = new \Modl\ConferenceDAO;
        $session = Session::start();

        $subscribed = $sd->getSubscribed();
        if($subscribed) {
            foreach($subscribed as $s) {
                array_push($arr,
                    [
                        'type'      => 'subscription',
                        'server'    => $s->server,
                        'title'     => $s->title,
                        'subid'     => $s->subid,
                        'tags'      => $s->tags,
                        'node'      => $s->node]);
            }
        }

        $conferences = $cd->getAll();
        if($conferences) {
            foreach($conferences as $c) {
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
          ->setTo($session->get('jid'))
          ->request();
    }

    function checkConnected($room, $resource = false)
    {
        if(!$this->validateRoom($room)) return;
        if($resource && !$this->validateResource($resource)) {
            Notification::append(null, $this->__('chatrooms.bad_id'));
            return;
        }

        $pd = new \Modl\PresenceDAO;

        if($resource == false) {
            $session = Session::start();
            $resource = $session->get('username');
        }

        $presence = $pd->getPresence($room, $resource);

        if($presence != null) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * @brief Get rooms users list
     */
    function getUsersList($room)
    {
        $cd = new \Modl\ContactDAO;
        return $cd->getPresences($room);
    }

    function prepareRooms($edit = false)
    {
        $view = $this->tpl();
        $cod = new \Modl\ConferenceDAO;

        $list = $cod->getAll();

        $connected = [];

        if(is_array($list)) {
            foreach($list as $key => $room) {
                if($this->checkConnected($room->conference, $room->nick)) {
                    $room->connected = true;
                    array_push($connected, $room);
                    unset($list[$key]);
                }
            }

            $connected = array_merge($connected, $list);
        }

        $view->assign('edit', $edit);
        $view->assign('conferences', $connected);
        $view->assign('room', $this->get('r'));

        return $view->draw('_rooms', true);
    }

    /**
     * @brief Validate the room
     *
     * @param string $room
     */
    private function validateRoom($room)
    {
        $validate_server = Validator::stringType()->noWhitespace()->length(6, 80);
        if(!$validate_server->validate($room)) return false;
        else return true;
    }

    /**
     * @brief Validate the resource
     *
     * @param string $resource
     */
    private function validateResource($resource)
    {
        $validate_resource = Validator::stringType()->length(2, 40);
        if(!$validate_resource->validate($resource)) return false;
        else return true;
    }

    function display()
    {
    }
}
