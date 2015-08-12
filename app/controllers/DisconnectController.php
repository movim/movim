<?php

class DisconnectController extends BaseController {
    function load() {
        $this->session_only = false;
    }

    function dispatch() {
        $user = new User();
        $user->desauth();
        $this->redirect('login');
    }
}
