<?php

use Movim\Controller\Base;
use Movim\User;

class NewsController extends Base
{
    function load()
    {
        $this->session_only = true;
    }

    function dispatch()
    {
        $this->page->setTitle(__('page.news'));

        if (!\App\User::me()->hasPubsub()) {
            $this->redirect('contact');
        }

        if (!\App\User::me()->isLogged()) {
            $this->redirect('login');
        }
    }
}
