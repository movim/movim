<?php

class LoginController extends BaseController {
    function load() {
        $this->session_only = false;
    }

    function dispatch() {
        $this->page->setTitle(__('title.login', APP_TITLE));
        
        $this->page->menuAddLink(__('page.home'), 'root', true);
        $this->page->menuAddLink(__('page.discover'), 'discover');
        $this->page->menuAddLink(__('page.pods'), 'pods');
        $this->page->menuAddLink(__('page.about'), 'about');

        $user = new User();
        if($user->isLogged())
            $this->redirect('root');
        
    }
}
