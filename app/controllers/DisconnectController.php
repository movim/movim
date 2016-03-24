<?php
use Movim\Controller\Base;

class DisconnectController extends Base
{
    function load() {
        $this->session_only = false;
    }

    function dispatch() {
        $pd = new modl\PresenceDAO();
        $pd->clearPresence();

        $s = \Sessionx::start();
        $s->destroy();

        Session::dispose();

        $this->redirect('login');
    }
}
