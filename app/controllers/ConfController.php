<?php

class ConfController extends BaseController {
    function load() {
        $this->session_only = true;
    }

    function dispatch() {
        $this->page->setTitle(__('title.configuration', APP_TITLE));
        $this->page->setColor('red');
    }
}
