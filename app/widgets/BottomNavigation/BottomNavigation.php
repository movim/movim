<?php

use Movim\Widget\Base;

class BottomNavigation extends Base
{
    public function load()
    {
        $this->addcss('bottomnavigation.css');

        $this->registerEvent('chat_counter', 'onCounter');
    }

    public function onCounter($count)
    {
        $this->rpc('MovimTpl.fill', '#chatcounter', $this->prepareChatButton($count));
    }

    public function display()
    {
        $this->view->assign('page', $this->_view);
        $this->view->assign('chatCounter', $this->prepareChatButton($this->user->unreads()));
    }

    private function prepareChatButton(int $count = 0)
    {
        $view = $this->tpl();
        $view->assign('count', $this->user->unreads());
        return $view->draw('_bottomnavigation_chat_counter');
    }
}
