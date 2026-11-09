<?php

namespace App\Controllers;

use App\Info;
use Movim\Controller\Base;

class SpaceController extends Base
{
    public function load()
    {
        if (!$this->user) {
            $space = Info::where('server', $this->fetchGet('s'))
                ->where('node', $this->fetchGet('n'))
                ->first();

            if (!$space) {
                $this->redirect('login');
            }

            $this->public = true;
        }
    }

    public function dispatch()
    {
        $this->page->setTitle(__('page.space'));

        if (!$this->user) {
            $this->js_check = false;
        }
    }
}
