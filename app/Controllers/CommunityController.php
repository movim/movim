<?php

namespace App\Controllers;

use App\Post;
use Movim\Controller\Base;

class CommunityController extends Base
{
    public function dispatch()
    {
        $this->page->setTitle(__('page.explore'));
        $this->jsCheck = false;

        if (isLogged() && $this->fetchGet('i')) {
            $post = Post::where('server', $this->fetchGet('s'))
                ->where('node', $this->fetchGet('n'))
                ->where('nodeid', $this->fetchGet('i'))
                ->first();

            if ($post) {
                $this->redirect('post', [
                    's' => $this->fetchGet('s'),
                    'n' => $this->fetchGet('n'),
                    'i' => $this->fetchGet('i'),
                ]);
            }
        }
    }
}
