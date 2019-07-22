<?php

use Movim\Controller\Base;

class BlogController extends Base
{
    public function load()
    {
        $this->public = true;
    }

    public function dispatch()
    {
        $this->page->setTitle(__('page.blog'));
        $this->page->disableJavascriptCheck();
    }
}
