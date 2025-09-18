<?php

namespace App\Widgets\BottomNavigation;

use Movim\Widget\Base;
use Moxl\Xec\Payload\Packet;

class BottomNavigation extends Base
{
    public function load()
    {
        $this->addcss('bottomnavigation.css');
        $this->addjs('bottomnavigation.js');

        $this->registerEvent('chat_counter', 'onCounter');
    }

    public function onCounter(Packet $packet)
    {
        $this->rpc('MovimUtils.setDataItem', '#bottomchatcounter', 'counter', $packet->content);
    }

    public function ajaxHttpRefresh()
    {
        $this->onCounter((new Packet)->pack($this->me->unreads()));
    }

    public function display()
    {
        $this->view->assign('page', $this->_view);
        $this->view->assign('bottomChatCounter', $this->me->unreads(null, false, true));
    }
}
