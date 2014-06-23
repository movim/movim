<?php

class AccountnextController extends BaseController {
    function load() {
        $this->session_only = false;
    }

    function dispatch() {
        $this->page->setTitle(__('title.account', APP_TITLE));
        
        $this->page->menuAddLink(__('page.home'), 'root');
        $this->page->menuAddLink(__('page.account_creation'), 'account', true);
    }
}
