<?php

use Movim\Controller\Base;

class CommunityController extends Base
{
    function load()
    {
        $this->session_only = true;
    }

    function dispatch()
    {
        $this->page->setTitle(__('page.communities'));

        $user = new User();
        if(!$user->isLogged()) {
            $this->redirect('node', [$this->fetchGet('s'), $this->fetchGet('n')]);
        }
    }
}
