<?php

class AccountController extends BaseController {
    function load() {
        $this->session_only = false;
    }

    function dispatch() {
        $this->page->setTitle(t('%s - Account', APP_TITLE));
        
        $this->page->menuAddLink(t('Home'), 'main');
        $this->page->menuAddLink(t('Account Creation'), 'account', true);
    }
}
