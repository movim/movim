<?php

use Movim\Controller\Base;

class AdminController extends Base
{
    public function load()
    {
        $this->session_only = true;
    }

    public function dispatch()
    {
        $this->page->setTitle(__('page.administration'));
    }
}
