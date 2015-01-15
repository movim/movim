<?php

use Moxl\Xec\Action\Presence\Muc;
use Moxl\Xec\Action\Bookmark\Get;
use Moxl\Xec\Action\Bookmark\Set;
use Moxl\Xec\Action\Presence\Unavailable;

class Rooms extends WidgetCommon
{
    function load()
    {
        $this->addjs('rooms.js');
        $this->registerEvent('bookmark_set_handle', 'onBookmark');
        $this->registerEvent('presence_muc_handle', 'onConnected');
        $this->registerEvent('presence_unavailable_handle', 'onDisconnected');
    }

    function onBookmark()
    {
        RPC::call('movim_fill', 'rooms_widget', $this->prepareRooms());
        Notification::append(null, $this->__('bookmarks.updated'));
        RPC::call('Rooms.refresh');
        RPC::call('MovimTpl.hidePanel');
    }

    function onConnected()
    {
        RPC::call('movim_fill', 'rooms_widget', $this->prepareRooms());
        Notification::append(null, $this->__('chatrooms.connected'));
        RPC::call('Rooms.refresh');
    }

    function onDisconnected()
    {
        // We reset the Chat view
        $c = new Chat();
        $c->ajaxGet();

        RPC::call('movim_fill', 'rooms_widget', $this->prepareRooms());
        Notification::append(null, $this->__('chatrooms.disconnected'));
        RPC::call('Rooms.refresh');
    }

    /**
     * @brief Display the add room form
     */
    function ajaxAdd()
    {
        $view = $this->tpl();

        $cd = new \Modl\ContactDAO;
        $view->assign('me', $cd->get());

        Dialog::fill($view->draw('_rooms_add', true));
    }

    /**
     * @brief Display the remove room confirmation
     */
    function ajaxRemoveConfirm($room)
    {
        $view = $this->tpl();

        $view->assign('room', $room);

        Dialog::fill($view->draw('_rooms_remove', true));
    }

    /**
     * @brief Display the remove room confirmation
     */
    function ajaxList($room)
    {
        $view = $this->tpl();

        $cd = new \Modl\ContactDAO;
        $view->assign('list', $cd->getPresence($room));

        Dialog::fill($view->draw('_rooms_list', true), true);
    }

    /**
     * @brief Remove a room
     */
    function ajaxRemove($room)
    {
        $cd = new \modl\ConferenceDAO();
        $cd->deleteNode($room);
        
        $this->setBookmark();
    }

    /**
     * @brief Join a chatroom
     */
    function ajaxJoin($jid, $nickname = false)
    {
        $p = new Muc;
        $p->setTo($jid);

        if($nickname != false) $p->setNickname($nickname);

        $p->request();
    }

    /**
     * @brief Exit a room
     *
     * @param string $room
     */
    function ajaxExit($room)
    {
        $session = \Sessionx::start();

        $pu = new Unavailable;
        $pu->setTo($room)
           ->setResource($session->username)
           ->request();
    }

    /**
     * @brief Display the add room form
     */
    function ajaxChatroomAdd($form) 
    {
        if(!filter_var($form['jid'], FILTER_VALIDATE_EMAIL)) {
            Notification::append(null, $this->__('chatrooms.bad_id'));
        } elseif(trim($form['name']) == '') {
            Notification::append(null, $this->__('chatrooms.empty_name'));
        } else {
            $item = array(
                    'type'      => 'conference',
                    'name'      => $form['name'],
                    'autojoin'  => $form['autojoin'],
                    'nick'      => $form['nick'],
                    'jid'       => $form['jid']);   
            $this->setBookmark($item);
            RPC::call('Dialog.clear');
        }
    }
    
    private function setBookmark($item = false) 
    {
        $arr = array();

        if($item) {
            array_push($arr, $item);
        }
        
        $sd = new \modl\SubscriptionDAO();
        $cd = new \modl\ConferenceDAO();

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
          ->setTo($this->user->getLogin())
          ->request();
    }

    function checkConnected($room, $resource = false)
    {
        $pd = new \modl\PresenceDAO();

        if($resource == false) {
            $session = \Sessionx::start();
            $resource = $session->user;
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
        $view->assign('conferences', $cod->getAll());

        return $view->draw('_rooms', true);
    }

    function display()
    {
        $this->view->assign('list', $this->prepareRooms());
    }
}
