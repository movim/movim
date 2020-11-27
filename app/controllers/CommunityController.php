<?php

use Movim\Controller\Base;

class CommunityController extends Base
{
    public function load()
    {
        $this->session_only = true;
    }

    public function dispatch()
    {
        $this->page->setTitle(__('page.explore'));

        if (!isLogged() && !empty($this->fetchGet('s') && !empty($this->fetchGet('n')))) {
            $this->redirect('node', [$this->fetchGet('s'), $this->fetchGet('n')]);
        }
    }
}
