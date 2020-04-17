<?php

use Movim\Widget\Base;

use Moxl\Xec\Action\Jingle\SessionReject;

class VisioLink extends Base
{
    public function load()
    {
        $this->addjs('visiolink.js');
        $this->addcss('visiolink.css');
    }

    public function ajaxReject($to, $id)
    {
        $this->rpc('Notification.incomingAnswer');
        $reject = new SessionReject;
        $reject->setTo($to)
               ->setId($id)
               ->request();
    }
}
