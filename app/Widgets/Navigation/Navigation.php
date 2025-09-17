<?php

namespace App\Widgets\Navigation;

use Movim\Widget\Base;
use Moxl\Xec\Payload\Packet;

class Navigation extends Base
{
    public function load()
    {
        $this->addjs('navigation.js');
        $this->registerEvent('chat_counter', 'onCounter');
    }

    public function onCounter(Packet $packet)
    {
        $this->rpc('MovimUtils.setDataItem', '#chatcounter', 'counter', $packet->content);
    }

    public function ajaxHttpRefresh()
    {
        $this->onCounter((new Packet)->pack($this->me->unreads()));
    }

    public function display()
    {
        $this->view->assign('page', $this->_view);
        $this->view->assign('chatCounter', $this->me->unreads(null, false, true));
    }
}
