<?php

use Movim\Controller\Base;
use Movim\User;

class NewsController extends Base
{
    public function load()
    {
        $this->session_only = true;
    }

    public function dispatch()
    {
        $this->page->setTitle(__('page.news'));

        if (!\App\User::me()->hasPubsub()) {
            $this->redirect('contact');
        }
    }
}
