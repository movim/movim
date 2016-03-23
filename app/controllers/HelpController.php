<?php
use Movim\Controller\Base;

class HelpController extends Base
{
    function load() {
        $this->session_only = true;
    }

    function dispatch() {
        $this->page->setTitle(__('page.help'));
    }
}
