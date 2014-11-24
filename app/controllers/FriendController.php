<?php

class FriendController extends BaseController {
    function load() {
        $this->session_only = true;
    }

    function dispatch() {
        if(isset($_GET['f']) && $_GET['f'] != "" ) {
            $user = new User();
            
            $cd = new \modl\ContactDAO();
            $contact = $cd->get($_GET['f']);

            if(isset($contact))
                $name = $contact->getTrueName();
            else
                $name = $_GET['f'];
            
            $this->page->setTitle(APP_TITLE.' - '.$name);
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
