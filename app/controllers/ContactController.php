<?php

use Movim\Controller\Base;

class ContactController extends Base
{
    public function load()
    {
        $this->session_only = true;
    }

    public function dispatch()
    {
        $this->page->setTitle(__('page.contacts'));

        if (empty($_GET['s'])) {
            $this->redirect('chat');
        }

        if (!isLogged() && $this->fetchGet('s')) {
            $this->redirect('blog', [$this->fetchGet('s')]);
        }
    }
}
