<?php

class NodeController extends BaseController {
    function load() {
        $this->session_only = false;
    }

    function dispatch() {
        $this->page->setTitle(__('page.groups'));
    }
}
