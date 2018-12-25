<?php

use Movim\Controller\Base;

class NotfoundController extends Base
{
    public function load()
    {
        $this->session_only = false;
    }

    public function dispatch()
    {
        $this->page->setTitle(__('page.not_found'));
        $this->page->disableJavascriptCheck();
    }
}
