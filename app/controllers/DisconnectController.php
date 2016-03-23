<?php
use Movim\Controller\Base;

class DisconnectController extends Base
{
    function load() {
        $this->session_only = false;
    }

    function dispatch() {
        $user = new User();
        $user->desauth();
        $this->redirect('login');
    }
}
