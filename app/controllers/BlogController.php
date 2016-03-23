<?php
use Movim\Controller\Base;

class BlogController extends Base
{
    function load() {
        $this->session_only = false;
        $this->public = true;
    }

    function dispatch() {
        $this->page->setTitle(__('page.blog'));
    }
}
