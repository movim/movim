<?php

namespace App\Controllers;

use Movim\Controller\Base;
use Movim\Cookie;
use Movim\Session;

class DisconnectController extends Base
{
    public function dispatch()
    {
        // Just in case
        if ($this->user) {
            $this->user->encryptedPasswords()->delete();
            requestAPI('disconnect', post: ['sid' => $this->user->session->id]);
        }
        Session::dispose();

        // Fresh cookie
        Cookie::renew();

        $this->redirect('login');
    }
}
