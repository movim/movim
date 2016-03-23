<?php
use Movim\Controller\Base;

class LoginController extends Base {
    function load() {
        $this->session_only = false;
    }

    function dispatch() {
        $this->page->setTitle(__('page.login'));

        $user = new User();
        if($user->isLogged()) {
            $this->redirect('root');
        }
    }
}
