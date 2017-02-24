<?php
use Movim\Controller\Base;

class DisconnectController extends Base
{
    function load()
    {
        $this->session_only = false;
    }

    function dispatch()
    {
        // Just in case
        requestURL('http://localhost:1560/disconnect/', 2, ['sid' => SESSION_ID]);
        Session::dispose();

        // Fresh cookie
        $session = \Sessionx::start();
        $session->renewCookie();

        $this->redirect('login');
    }
}
