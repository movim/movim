<?php

use Movim\Widget\Base;
use Movim\RPC;

class Toast extends Base
{
    public function load()
    {
        $this->addjs('toast.js');
    }

    public static function send($title)
    {
        RPC::call('Toast.send', $title);
    }
}