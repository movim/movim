<?php

class MediaController extends BaseController {
    function load() {
        $this->session_only = true;
    }

    function dispatch() {
        $this->page->setTitle(APP_TITLE.' - '.t('Media'));
        
        $this->page->menuAddLink(t('Home'), 'main');
        $this->page->menuAddLink(t('News'), 'news');
        $this->page->menuAddLink(t('Explore'), 'explore');
        $this->page->menuAddLink(t('Profile'), 'profile');
        $this->page->menuAddLink(t('Media'), 'media', true);
        $this->page->menuAddLink(t('Configuration'), 'conf');
        $this->page->menuAddLink(t('Help'), 'help');
    }
}
