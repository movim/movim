<?php
use Movim\Controller\Base;

class TagController extends Base
{
    function load() {
        $this->session_only = false;
    }

    function dispatch() {
        $this->page->setTitle(__('page.tag'));
    }
}
