<?php

class NodeconfigController extends BaseController {
    function load() {
        $this->session_only = true;
    }

    function dispatch() {
        if(isset($_GET['s']) && $_GET['s'] != "" ) {
            $this->page->setTitle(__('title.node_configuration', APP_TITLE));
            
            $this->page->menuAddLink(__('page.home'), 'root');
            $this->page->menuAddLink(__('page.news'), 'news');
            $this->page->menuAddLink(__('page.explore'), 'explore');
            $this->page->menuAddLink(__('page.profile'), 'profile');
            $this->page->menuAddLink(__('page.media'), 'media');
            $this->page->menuAddLink(__('page.configuration'), 'conf', false, true);
            $this->page->menuAddLink(__('page.help'), 'help', false, true);
        } else {
            $this->name = 'main';
        }
    }
}
