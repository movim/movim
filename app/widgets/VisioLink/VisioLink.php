<?php

use Movim\Widget\Base;

class VisioLink extends Base
{
    public function load()
    {
        $this->addjs('visiolink.js');
        $this->addcss('visiolink.css');
    }

    public function ajaxDecline($to, $id)
    {
        $this->rpc('Notification.incomingAnswer');
        $visio = new Visio;
        $visio->ajaxTerminate($to, 'decline', $id);
    }
}
