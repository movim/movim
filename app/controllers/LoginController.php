<?php

class LoginController extends BaseController {
    function load() {
        $this->session_only = false;
    }

    function dispatch() {
        $this->page->setTitle(t('%s - Login to Movim', APP_TITLE));
        
        $this->page->menuAddLink(t('Home'), 'main', true);
        $this->page->menuAddLink(t('Discover'), 'discover');
        $this->page->menuAddLink(t('About'), 'about');
    }
}
