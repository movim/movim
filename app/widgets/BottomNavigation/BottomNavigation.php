<?php

use Movim\Widget\Base;

class BottomNavigation extends Base
{
    public function load()
    {
        $this->addcss('bottomnavigation.css');

        $this->registerEvent('message', 'onMessage');
        $this->registerEvent('chat_open', 'onMessage', 'chat');
        $this->registerEvent('chat_open_room', 'onMessage', 'chat');
    }

    public function onMessage($packet)
    {
        $this->rpc('MovimTpl.fill', '#bottomchatcounter', $this->prepareChatButton());
    }

    public function prepareChatButton()
    {
        $view = $this->tpl();
        $view->assign('count', $this->user->unreads());
        return $view->draw('_bottomnavigation_chat_counter');
    }

    public function display()
    {
        $this->view->assign('page', $this->_view);
        $this->view->assign('chatCounter', $this->prepareChatButton());
    }
}
