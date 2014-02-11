<?php
/**
 * Explore the XMPP network
 */
class ExploreController extends BaseController {
    function load() {
        $this->session_only = true;
    }

    function dispatch() {
        $this->page->setTitle(t('%s - Explore', APP_TITLE));
    
        $this->page->menuAddLink(t('Home'), 'main');
        $this->page->menuAddLink(t('News'), 'news');
        $this->page->menuAddLink(t('Explore'), 'explore', true);
        $this->page->menuAddLink(t('Profile'), 'profile');
        $this->page->menuAddLink(t('Media'), 'media');
        $this->page->menuAddLink(t('Configuration'), 'conf', false, true);
        $this->page->menuAddLink(t('Help'), 'help', false, true);
    }
}
