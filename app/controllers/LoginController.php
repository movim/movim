<?php
use Movim\Controller\Base;

class LoginController extends Base {
    function load() {
        $this->session_only = false;
    }

    function dispatch() {
        $session = \Sessionx::start();
        //requestURL('http://localhost:1560/unregister/', 2, ['sid' => $session->sessionid]);

        $this->page->setTitle(__('page.login'));

        $user = new User();
        if($user->isLogged()) {
            $this->redirect('root');
        }
    }
}
