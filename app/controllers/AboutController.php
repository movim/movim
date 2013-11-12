<?php

class AboutController extends BaseController {
    function load() {
        $this->session_only = false;
    }

    function dispatch() {
        $this->page->setTitle(t('%s - About', APP_TITLE));
        
        $this->page->menuAddLink(t('Home'), 'main');
        $this->page->menuAddLink(t('Discover'), 'discover');
        $this->page->menuAddLink(t('About'), 'about', true);
    }
}
