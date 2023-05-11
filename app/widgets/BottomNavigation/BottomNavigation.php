<?php

use Movim\Widget\Base;

class BottomNavigation extends Base
{
    public function load()
    {
        $this->addcss('bottomnavigation.css');
        $this->addjs('bottomnavigation.js');

        $this->registerEvent('chat_counter', 'onCounter');
    }

    public function onCounter($count)
    {
        $this->rpc('MovimUtils.setDataItem', '#bottomchatcounter', 'counter', $count);
    }

    public function ajaxHttpRefresh()
    {
        $this->onCounter($this->user->unreads());
    }

    public function display()
    {
        $this->view->assign('page', $this->_view);
        $this->view->assign('bottomChatCounter', $this->user->unreads(null, false, true));
    }
}
