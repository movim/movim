<?php

class VisioLink extends \Movim\Widget\Base
{
    function load()
    {
        $this->addjs('visiolink.js');
        $this->addcss('visiolink.css');
    }

    function ajaxDecline($to)
    {
        $visio = new Visio;
        $visio->ajaxTerminate($to, 'decline');
    }
}
