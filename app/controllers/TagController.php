<?php

use Movim\Controller\Base;

class TagController extends Base
{
    public function load()
    {
        $this->session_only = false;
    }

    public function dispatch()
    {
        $this->page->setTitle(__('page.tag'));
        $this->page->disableJavascriptCheck();
    }
}
