<?php

use Movim\Controller\Base;
use Movim\User;

class HomeController extends Base
{
    function load()
    {
    }

    function dispatch()
    {
        $this->page->setTitle(__('page.home'));

        $user = new User;

        if($user->isLogged()) {
            $this->redirect('news');
        }
    }
}
