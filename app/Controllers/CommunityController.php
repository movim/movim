<?php

namespace App\Controllers;

use Movim\Controller\Base;

class CommunityController extends Base
{
    public function dispatch()
    {
        $this->page->setTitle(__('page.explore'));

        if (isLogged() && $this->fetchGet('i')) {
            $this->redirect('post', [
                's' => $this->fetchGet('s'),
                'n' => $this->fetchGet('n'),
                'i' => $this->fetchGet('i'),
            ]);
        }
    }
}
