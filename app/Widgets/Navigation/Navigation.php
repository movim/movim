<?php

namespace App\Widgets\Navigation;

use Movim\Widget\Base;

class Navigation extends Base
{
    public function load()
    {
        $this->addjs('navigation.js');
        $this->registerEvent('chat_counter', 'onCounter');
    }

    public function onCounter($count)
    {
        $this->rpc('MovimUtils.setDataItem', '#chatcounter', 'counter', $count);
    }

    public function ajaxHttpRefresh()
    {
        $this->onCounter($this->user->unreads());
    }

    public function display()
    {
        $this->view->assign('page', $this->_view);
        $this->view->assign('chatCounter', $this->user->unreads(null, false, true));
    }
}
