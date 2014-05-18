<?php

class BlogController extends BaseController {
    function load() {
        $this->session_only = false;
    }

    function dispatch() {
        $this->page->setTitle(__('title.blog', APP_TITLE));
        
        $this->page->menuAddLink(__('page.home'), 'main');
        $this->page->menuAddLink(__('page.blog'), 'blog', true);
    }
}
