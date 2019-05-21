<?php

use Movim\Widget\Base;

class Navigation extends Base
{
    public function load()
    {
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
        $view->assign('count', $count);
        return $view->draw('_navigation_chat_counter');
    }
}
