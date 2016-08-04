<?php
use Movim\Controller\Base;

class GroupController extends Base
{
    function load() {
        $this->session_only = true;
    }

    function dispatch() {
        $this->page->setTitle(__('page.groups'));

        $user = new User();
        if(!$user->isLogged()) {
            $this->redirect('node', [$this->fetchGet('s'), $this->fetchGet('n')]);
        }
    }
}
