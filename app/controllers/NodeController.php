<?php

class NodeController extends BaseController {
    function load() {
        $this->session_only = true;
    }

    function dispatch() {
        if(isset($_GET['s']) && $_GET['s'] != "" ) {
            $this->page->setTitle(APP_TITLE.' - Node');
            
            $this->page->menuAddLink(t('Home'), 'main');
            $this->page->menuAddLink(t('News'), 'news');
            $this->page->menuAddLink(t('Explore'), 'explore');
            $this->page->menuAddLink(t('Profile'), 'profile');
            $this->page->menuAddLink(t('Media'), 'media');
            $this->page->menuAddLink(t('Configuration'), 'conf', false, true);
            $this->page->menuAddLink(t('Help'), 'help', false, true);
        } else {
            $this->name = 'main';
        }
    }
}
