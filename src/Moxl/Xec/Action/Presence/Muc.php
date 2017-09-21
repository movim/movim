<?php

namespace Moxl\Xec\Action\Presence;

use Moxl\Xec\Action;
use Moxl\Stanza\Presence;

use Movim\Session;

class Muc extends Action
{
    private $_to;
    private $_nickname;
    private $_mam = false;
    private $_mam2 = false;

    public function request()
    {
        $this->store();

        $session = Session::start();

        if(empty($this->_nickname)) {
            $this->_nickname = $session->get('username');
        }

        if($this->_mam == false) {
            // We clear all the old messages
            $md = new \Modl\MessageDAO;
            $md->deleteContact($this->_to);
        }

        Presence::muc($this->_to, $this->_nickname, $this->_mam);
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

    public function enableMAM()
    {
        $this->_mam = true;
        return $this;
    }

    public function enableMAM2()
    {
        $this->_mam2 = true;
        return $this;
    }

    public function handle($stanza, $parent = false)
    {
        $p = new \Modl\Presence;
        $p->setPresence($stanza);

        $pd = new \Modl\PresenceDAO;
        $pd->set($p);

        if($this->_mam) {
            $md = new \Modl\MessageDAO;
            $message = $md->getLastReceivedItem($this->_to);

            $g = new \Moxl\Xec\Action\MAM\Get;
            $g->setTo($this->_to)
              ->setJid($this->_to)
              ->setLimit(300);

            if(!empty($message)) {
                $g->setStart(strtotime($message->published));
            }

            if($this->_mam2) {
                $g->setVersion('2');
            }

            $g->request();
        }

        $this->pack($p);
        $this->deliver();
    }

    public function errorRegistrationRequired($stanza, $parent = false)
    {
        $this->pack($this->_to);
        $this->deliver();
    }

    public function errorRemoteServerNotFound($stanza, $parent = false)
    {
        $this->pack($this->_to);
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
