<?php
use Movim\Controller\Base;

class ContactController extends Base
{
    function load()
    {
        $this->session_only = true;
    }

    function dispatch()
    {
        $this->page->setTitle(__('page.contacts'));

        $user = new User;
        if(!$user->isLogged() && $this->fetchGet('f')) {
            $this->redirect('blog', [$this->fetchGet('f')]);
        }
    }
}
