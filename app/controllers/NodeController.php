<?php
use Movim\Controller\Base;

class NodeController extends Base
{
    function load() {
        $this->session_only = false;
        $this->public = true;
    }

    function dispatch() {
        $this->page->setTitle(__('page.groups'));
    }
}
