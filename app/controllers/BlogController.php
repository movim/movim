<?php

use Movim\Controller\Base;

class BlogController extends Base
{
    public function load()
    {
        $this->session_only = false;
        $this->public = true;
    }

    public function dispatch()
    {
        $this->page->setTitle(__('page.blog'));
        $this->page->disableJavascriptCheck();
    }
}
