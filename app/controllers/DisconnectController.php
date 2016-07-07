<?php
use Movim\Controller\Base;

class DisconnectController extends Base
{
    function load() {
        $this->session_only = false;
    }

    function dispatch() {
        $session = \Sessionx::start();
        requestURL('http://localhost:1560/disconnect/', 2, ['sid' => $session->sessionid]);

        Session::dispose();

        $this->redirect('login');
    }
}
