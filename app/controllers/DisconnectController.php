<?php

use Movim\Controller\Base;
use Movim\Cookie;
use Movim\Session;

class DisconnectController extends Base
{
    public function dispatch()
    {
        // Just in case
        requestAPI('disconnect', 2, ['sid' => SESSION_ID]);
        Session::dispose();

        // Fresh cookie
        Cookie::renew();

        $this->redirect('login');
    }
}
