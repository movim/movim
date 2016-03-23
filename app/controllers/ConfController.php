<?php
use Movim\Controller\Base;

class ConfController extends Base
{
    function load() {
        $this->session_only = true;
    }

    function dispatch() {
        $this->page->setTitle(__('page.configuration'));
    }
}
