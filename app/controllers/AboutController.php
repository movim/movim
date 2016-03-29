<?php
use Movim\Controller\Base;

class AboutController extends Base {
    function load() {
        $this->session_only = false;
    }

    function dispatch() {
        $this->page->setTitle(__('page.about'));
    }
}
