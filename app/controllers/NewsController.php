<?php

use Movim\Controller\Base;

class NewsController extends Base
{
    function load()
    {
        $this->session_only = true;
    }

    function dispatch()
    {
        $this->page->setTitle(__('page.news'));

        $user = new User;

        if(!$user->isSupported('pubsub')) {
            $this->redirect('contact');
        }

        if(!$user->isLogged()) {
            $pd = new \Modl\PostnDAO;
            $p  = $pd->get(
                $this->fetchGet('s'),
                $this->fetchGet('n'),
                $this->fetchGet('i')
            );

            if($p) {
                if($p->isMicroblog()) {
                    $this->redirect('blog', [$p->origin, $p->nodeid]);
                } else {
                    $this->redirect('node', [$p->origin, $p->node, $p->nodeid]);
                }
            } else {
                $this->redirect('login');
            }
        }
    }
}
