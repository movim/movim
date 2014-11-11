<?php

class ConfController extends BaseController {
    function load() {
        $this->session_only = true;
    }

    function dispatch() {
        $this->page->setTitle(__('title.configuration', APP_TITLE));
        
        $this->page->menuAddLink(__('page.home'), 'root');
        $this->page->menuAddLink(__('page.news'), 'news');
        $this->page->menuAddLink(__('page.explore'), 'explore');
        $this->page->menuAddLink(__('page.profile'), 'profile');
        $this->page->menuAddLink(__('page.media'), 'media');
        $this->page->menuAddLink(__('page.configuration'), 'conf', true, true);
        $this->page->menuAddLink(__('page.help'), 'help', false, true);
    }
}
