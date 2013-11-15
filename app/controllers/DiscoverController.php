<?php
/**
 * Discover the XMPP network from the internet
 */
class DiscoverController extends BaseController {
    function load() {
        $this->session_only = false;
    }

    function dispatch() {
        $this->page->setTitle(t('%s - Discover', APP_TITLE));
        
        $this->page->menuAddLink(t('Home'), 'main');
        $this->page->menuAddLink(t('Discover'), 'discover', true);
        $this->page->menuAddLink(t('About'), 'about');
    }
}
