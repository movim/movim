<?php

class MainController extends BaseController {
    function load() {
        $this->session_only = true;
    }

    function dispatch() {
        $this->page->setTitle(t('%s - Welcome to Movim', APP_TITLE));
        $this->page->menuAddLink(t('Home'), 'main', true);
        $this->page->menuAddLink(t('News'), 'news');
        $this->page->menuAddLink(t('Explore'), 'explore');
        $this->page->menuAddLink(t('Profile'), 'profile');
        $this->page->menuAddLink(t('Media'), 'media');
        $this->page->menuAddLink(t('Configuration'), 'conf');
        $this->page->menuAddLink(t('Help'), 'help');
    }
}
