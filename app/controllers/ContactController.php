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

        if (empty($_GET['s'])) {
            $this->redirect('chat');
        }

        $user = new \App\User;
        if (!$user->isLogged() && $this->fetchGet('s')) {
            $this->redirect('blog', [$this->fetchGet('s')]);
        }
    }
}
