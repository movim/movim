<?php

namespace App\Controllers;

use Movim\Controller\Base;

class NotfoundController extends Base
{
    public function dispatch()
    {
        $this->page->setTitle(__('page.not_found'));
        $this->jsCheck = false;
    }
}
