<?php

class NotfoundController extends BaseController {
    function load() {
        $this->session_only = false;
    }

    function dispatch() {
        $this->page->setTitle(APP_TITLE. ' - 404');
        
        $this->page->menuAddLink(t('Home'), 'main');
    }
}
