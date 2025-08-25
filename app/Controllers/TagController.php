<?php

namespace App\Controllers;

use Movim\Controller\Base;

class TagController extends Base
{
    public function dispatch()
    {
        $this->page->setTitle(__('page.tag'));
        $this->jsCheck = false;
    }
}
