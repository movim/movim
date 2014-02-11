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
