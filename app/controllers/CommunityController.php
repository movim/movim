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
        $this->page->setTitle(__('page.communities'));

        $user = new \App\User;
        if (!$user->isLogged()) {
            $this->redirect('node', [$this->fetchGet('s'), $this->fetchGet('n')]);
        }
    }
}
