<?php

class MediaController extends BaseController {
    function load() {
        $this->session_only = true;
    }

    function dispatch() {
        $this->page->setTitle(__('title.media', APP_TITLE));
    }
}
