<?php

class AboutController extends BaseController {
    function load() {
        $this->session_only = false;
    }

    function dispatch() {
        $this->page->setTitle(__('title.about', APP_TITLE));
        
        $this->page->menuAddLink(__('page.home'), 'root');
        $this->page->menuAddLink(__('page.discover'), 'discover');
        $this->page->menuAddLink(__('page.pods'), 'pods');
        $this->page->menuAddLink(__('page.about'), 'about', true);
    }
}
