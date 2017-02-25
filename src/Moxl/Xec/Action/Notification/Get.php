<?php

namespace Moxl\Xec\Action\Notification;

use Moxl\Xec\Action;
use Moxl\Stanza\Notification;

use Movim\Session;

class Get extends Action
{
    private $_to;

    public function request()
    {
        $this->store();
        Notification::get($this->_to);
    }

    public function setTo($to)
    {
        $this->_to = $to;
        return $this;
    }

    public function handle($stanza, $parent = false)
    {
        $session = Session::start();
        $session->set('activenotifs', []);
        if($stanza->pubsub->items->item) {
            foreach($stanza->pubsub->items->item as $item) {
                $this->event('notification', $item);
            }

            $this->event('notifications');
        } else {
            $this->event('nonotification');
        }
    }

    public function errorFeatureNotImplemented($stanza)
    {
        $this->event('nonotification');
    }

    public function errorItemNotFound($stanza)
    {
        $this->event('nonotification');
    }

    public function errorNotAuthorized($stanza)
    {
        $this->event('nonotificationautorized');
    }

    public function errorNotAllowed($stanza)
    {
        $this->errorItemNotFound($stanza);
    }

}
