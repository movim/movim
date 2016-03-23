<?php
use Movim\Controller\Base;

class MainController extends Base
{
    function load() {
        $this->session_only = true;
    }

    function dispatch() {
        $this->page->setTitle(__('page.home'));
    }
}
