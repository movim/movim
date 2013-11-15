<?php

class ProfileController extends BaseController {
    function load() {
        $this->session_only = true;
    }

    function dispatch() {
        $this->page->setTitle(t('%s - Profile', APP_TITLE));
        
        $this->page->menuAddLink(t('Home'), 'main');
        $this->page->menuAddLink(t('News'), 'news');
        $this->page->menuAddLink(t('Explore'), 'explore');
        $this->page->menuAddLink(t('Profile'), 'profile', true);
        $this->page->menuAddLink(t('Media'), 'media');
        $this->page->menuAddLink(t('Configuration'), 'conf');
        $this->page->menuAddLink(t('Help'), 'help');
    }
}
