<?php

class HelpController extends BaseController {
    function load() {
        $this->session_only = true;
    }

    function dispatch() {
        $this->page->setTitle(__('title.help', APP_TITLE));
        $this->page->setColor('indigo');
    }
}
