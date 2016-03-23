<?php
use Movim\Controller\Base;

class ChatController extends Base
{
    function load() {
        $this->session_only = true;
    }

    function dispatch() {
        $this->page->setTitle(__('page.chats'));
    }
}
