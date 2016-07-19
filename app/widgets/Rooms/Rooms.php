<?php

use Moxl\Xec\Action\Presence\Muc;
use Moxl\Xec\Action\Bookmark\Get;
use Moxl\Xec\Action\Bookmark\Set;
use Moxl\Xec\Action\Presence\Unavailable;

use Respect\Validation\Validator;

class Rooms extends \Movim\Widget\Base
{
    function load()
    {
        $this->addjs('rooms.js');
        $this->addcss('rooms.css');
        $this->registerEvent('message', 'onMessage');
        $this->registerEvent('bookmark_set_handle', 'onBookmark');
        $this->registerEvent('presence_muc_handle', 'onConnected');
        $this->registerEvent('presence_unavailable_handle', 'onDisconnected');
        $this->registerEvent('presence_muc_errorconflict', 'onConflict');
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

    function onBookmark()
    {
        $this->refreshRooms();
        RPC::call('MovimTpl.hidePanel');
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

    private function refreshRooms()
    {
        RPC::call('movim_fill', 'rooms_widget', $this->prepareRooms());
        RPC::call('Rooms.refresh');
    }

    /**
     * @brief Get the Rooms
     */
    public function ajaxDisplay()
    {
        $this->refreshRooms();
    }

    /**
     * @brief Display the add room form
     */
    function ajaxAdd()
    {
        $view = $this->tpl();

        $id = new \Modl\ItemDAO;
        $item = $id->getConference($this->user->getServer());

        if($item) {
            $view->assign('server', $item->jid);
        }

        $view->assign('username', $this->user->getUser());

        Dialog::fill($view->draw('_rooms_add', true));
    }

    /**
     * @brief Edit a room configuration
     */
    function ajaxEdit($room)
    {
        $view = $this->tpl();
        $cd = new \Modl\ConferenceDAO;

        $view->assign('room', $cd->get($room));
        $view->assign('username', $this->user->getUser());

        Dialog::fill($view->draw('_rooms_add', true));
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

        $cd = new \Modl\ContactDAO;
        $view->assign('list', $cd->getPresences($room));

        Dialog::fill($view->draw('_rooms_list', true), true);
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

            $item = array(
                    'type'      => 'conference',
                    'name'      => $form['name'],
                    'autojoin'  => $form['autojoin'],
                    'nick'      => $form['nick'],
                    'jid'       => $form['jid']);
            $this->setBookmark($item);
            RPC::call('Dialog_ajaxClear');
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

        if($sd->getSubscribed()) {
            foreach($sd->getSubscribed() as $s) {
                array_push($arr,
                    array(
                        'type'      => 'subscription',
                        'server'    => $s->server,
                        'title'     => $s->title,
                        'subid'     => $s->subid,
                        'tags'      => unserialize($s->tags),
                        'node'      => $s->node));
            }
        }

        foreach($cd->getAll() as $c) {
            array_push($arr,
                array(
                    'type'      => 'conference',
                    'name'      => $c->name,
                    'autojoin'  => $c->autojoin,
                    'nick'      => $c->nick,
                    'jid'       => $c->conference));
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
            $session = \Session::start();
            $resource = $session->get('username');
        }

        $presence = $pd->getPresence($room, $resource);

        if($presence != null) {
            return true;
        } else {
            return false;
        }
    }

    function prepareRooms()
    {
        $view = $this->tpl();
        $cod = new \modl\ConferenceDAO();

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
