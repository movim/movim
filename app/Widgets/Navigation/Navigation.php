<?php

namespace App\Widgets\Navigation;

use App\Post;
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

    public function ajaxHttpPublish()
    {
        $this->dialog($this->view('_navigation_publish', [
            'draft' => $this->me->drafts()
                ->where('server', $this->me->id)
                ->where('node', Post::MICROBLOG_NODE)
                ->where('nodeid', '')
                ->first()
        ]));
    }

    public function display()
    {
        $this->view->assign('page', $this->_view);
    }
}
