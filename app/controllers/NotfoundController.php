<?php
use Movim\Controller\Base;

class NotfoundController extends Base
{
    function load() {
        $this->session_only = false;
    }

    function dispatch() {
        $this->page->setTitle(__('title.not_found', APP_TITLE));
    }
}
