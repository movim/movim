<?php

use Movim\Controller\Base;
use Movim\Cookie;
use Movim\Session;

use App\User;

class DisconnectController extends Base
{
    public function dispatch()
    {
        // Just in case
        User::me()->encryptedPasswords()->delete();
        requestAPI('disconnect', 2, ['sid' => SESSION_ID]);
        Session::dispose();

        // Fresh cookie
        Cookie::renew();

        $this->redirect('login');
    }
}
