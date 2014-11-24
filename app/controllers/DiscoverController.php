<?php
/**
 * Discover the XMPP network from the internet
 */
class DiscoverController extends BaseController {
    function load() {
        $this->session_only = false;
    }

    function dispatch() {
        $this->page->setTitle(__('title.discover', APP_TITLE));
        
        $this->page->menuAddLink(__('page.home'), 'root');
        $this->page->menuAddLink(__('page.discover'), 'discover', true);
        $this->page->menuAddLink(__('page.pods'), 'pods');
        $this->page->menuAddLink(__('page.about'), 'about');
    }
}
