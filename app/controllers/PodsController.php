<?php
/**
 * Discover the other Movim Pods using the Movim API
 */
class PodsController extends BaseController {
    function load() {
        $this->session_only = false;
    }

    function dispatch() {
        $this->page->setTitle(__('title.discover', APP_TITLE));
        
        $this->page->menuAddLink(__('page.home'), 'root');
        $this->page->menuAddLink(__('page.discover'), 'discover');
        $this->page->menuAddLink(__('page.pods'), 'pods', true);
        $this->page->menuAddLink(__('page.about'), 'about');
    }
}
