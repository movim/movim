<?php

class NewsController extends BaseController {
    function load() {
        $this->session_only = true;
    }

    function dispatch() {
        $this->page->setTitle(t('%s - News', APP_TITLE));
        
        $this->page->menuAddLink(t('Home'), 'main');
        $this->page->menuAddLink(t('News'), 'news', true);
        $this->page->menuAddLink(t('Explore'), 'explore');
        $this->page->menuAddLink(t('Profile'), 'profile');
        $this->page->menuAddLink(t('Media'), 'media');
        $this->page->menuAddLink(t('Configuration'), 'conf', false, true);
        $this->page->menuAddLink(t('Help'), 'help', false, true);
    }
}
