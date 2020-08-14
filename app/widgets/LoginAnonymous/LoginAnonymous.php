<?php

use Respect\Validation\Validator;

use Movim\Widget\Base;
use Movim\Session;

class LoginAnonymous extends Base
{
    public function load()
    {
        $this->addjs('loginanonymous.js');
        $this->registerEvent('session_start_handle', 'onStart');
    }

    public function onStart($packet)
    {
        $session = Session::start();
        if ($session->get('mechanism') == 'ANONYMOUS') {
            $this->rpc('Rooms.anonymousJoin');
        }
    }

    public function ajaxLogin($username)
    {
        $validate_user = Validator::stringType()->length(4, 40);
        if (!$validate_user->validate($username)) {
            Toast::send($this->__('login_anonymous.bad_username'));
            return;
        }

        $host = 'anonymous.jappix.com';
        $password = 'AmISnowden?';

        // We try to get the domain
        $domain = \Moxl\Utils::getDomain($host);

        // We launch the XMPP socket
        $this->rpc('register', $host);

        // We set the username in the session
        $s = Session::start();
        $s->set('username', $username);

        $s = new \Modl\Sessionx;
        $s->init($username, $password, $host);
        $s->loadMemory();
        $sd->set($s);

        \Moxl\Stanza\Stream::init($host);
    }
}
