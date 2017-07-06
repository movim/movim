<?php

namespace Moxl\Xec\Action\Presence;

use Moxl\Xec\Action;
use Moxl\Stanza\Presence;

use Movim\Session;

class Muc extends Action
{
    private $_to;
    private $_nickname;

    public function request()
    {
        $this->store();

        $session = Session::start();

        if(empty($this->_nickname)) {
            $this->_nickname = $session->get('username');
        }

        // We clear all the old messages
        $md = new \Modl\MessageDAO;
        $md->deleteContact($this->_to);

        Presence::muc($this->_to, $this->_nickname);
    }

    public function setTo($to)
    {
        $this->_to = $to;
        return $this;
    }

    public function setNickname($nickname)
    {
        $this->_nickname = $nickname;
        return $this;
    }

    public function handle($stanza, $parent = false)
    {
        $p = new \Modl\Presence;
        $p->setPresence($stanza);

        $pd = new \Modl\PresenceDAO;
        $pd->set($p);

        $this->deliver();
    }

    public function errorRegistrationRequired($stanza, $parent = false)
    {
        $this->deliver();
    }

    public function errorConflict($stanza, $message)
    {
        if(substr_count($this->_nickname, '_') > 5) {
            $this->deliver();
        } else {
            $this->setNickname($this->_nickname.'_');
            $this->request();
        }
    }
}
