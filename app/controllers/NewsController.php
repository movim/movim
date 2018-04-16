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
            $p = \App\Post::where('server', $this->fetchGet('s'))
                          ->where('node', $this->fetchGet('n'))
                          ->where('nodeid', $this->fetchGet('i'))
                          ->first();

            if ($p) {
                if ($p->isMicroblog()) {
                    $this->redirect('blog', [$p->server, $p->nodeid]);
                } else {
                    $this->redirect('node', [$p->server, $p->node, $p->nodeid]);
                }
            } else {
                $this->redirect('login');
            }
        }
    }
}
