<?php
use Movim\Controller\Base;

class ShareController extends Base
{
    function load() {
        $this->session_only = true;
    }

    function dispatch() {
        $this->page->setTitle(__('page.share'));
    }
}
