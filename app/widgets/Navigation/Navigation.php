<?php

use Movim\Widget\Base;

class Navigation extends Base
{
    public function load()
    {
        $this->registerEvent('message', 'onMessage');
        $this->registerEvent('chat_open', 'onMessage', 'chat');
        $this->registerEvent('chat_open_room', 'onMessage', 'chat');
    }

    public function onMessage($packet)
    {
        $this->rpc('MovimTpl.fill', '#chatcounter', $this->prepareChatButton());
    }

    public function display()
    {
        $this->view->assign('page', $this->_view);
        $this->view->assign('chatCounter', $this->prepareChatButton());
    }

    public function prepareChatButton()
    {
        $view = $this->tpl();
        $view->assign('count', $this->user->unreads());
        return $view->draw('_navigation_chat_counter');
    }
}
