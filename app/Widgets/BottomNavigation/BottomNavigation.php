<?php

namespace App\Widgets\BottomNavigation;

use App\Presence;
use Movim\Widget\Base;
use Moxl\Xec\Payload\Packet;

class BottomNavigation extends Base
{
    public function load()
    {
        $this->addcss('bottomnavigation.css');
        $this->addjs('bottomnavigation.js');

        $this->registerEvent('chat_counter', 'onCounter');
        $this->registerEvent('mypresence', 'onMyPresence');
    }

    public function onCounter(Packet $packet)
    {
        $counter['chat'] = $packet->content;
        $this->rpc('BottomNagivation.setChatNotification', $counter);
    }

    public function onMyPresence(Packet $packet)
    {
        $this->rpc('MovimTpl.fill', '#bottomnavigation_me', $this->prepareMe());
    }

    public function ajaxHttpRefresh()
    {
        $this->onCounter((new Packet)->pack($this->me->unreads()));
        $this->rpc('MovimTpl.fill', '#bottomnavigation_me', $this->prepareMe());
    }

    public function prepareMe(): string
    {
        return $this->view('_bottomnavigation_me', [
            'me' => $this->me->contact ?? new \App\Contact,
            'presence' => Presence::where('resource', $this->me->session->resource)->firstOrNew(),
            'presencetxt' => getPresencesTxt()
        ]);
    }

    public function display()
    {
        $this->view->assign('me', $this->me->contact ?? new \App\Contact);
        $this->view->assign('page', $this->_view);
    }
}
