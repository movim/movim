<?php

use Movim\Controller\Base;
use Movim\Cookie;
use Movim\Session;

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
        Cookie::renew();

        $this->redirect('login');
    }
}
