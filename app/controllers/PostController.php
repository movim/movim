<?php

use Movim\Controller\Base;

class PostController extends Base
{
    public function load()
    {
        $this->session_only = true;
    }

    public function dispatch()
    {
        $this->page->setTitle(__('page.post'));

        if (!isLogged()) {
            $post = App\Post::where([
                'server' => $this->fetchGet('s'),
                'node' => $this->fetchGet('n'),
                'nodeid' => $this->fetchGet('i'),
                'open' => true
            ])->first();

            if ($post) {
                if ($post->isMicroblog()) {
                    $this->redirect('blog', [$this->fetchGet('s'), $this->fetchGet('i')]);
                } else {
                    $this->redirect('node', [$this->fetchGet('s'),$this->fetchGet('n'), $this->fetchGet('i')]);
                }
            } else {
                $this->redirect('notfound');
            }
        }
    }
}
