<?php

class AboutController extends BaseController {
    function load() {
        $this->session_only = false;
    }

    function dispatch() {
        $this->page->setTitle(__('title.about', APP_TITLE));
    }
}
