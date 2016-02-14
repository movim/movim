<?php

class NodeController extends BaseController {
    function load() {
        $this->session_only = false;
        $this->public = true;
    }

    function dispatch() {
        $this->page->setTitle(__('page.groups'));
    }
}
