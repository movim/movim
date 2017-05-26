<?php

use Movim\Controller\Base;
use Movim\User;

use Respect\Validation\Validator;

class LoginController extends Base
{
    function load()
    {
        $this->session_only = false;
    }

    function dispatch()
    {
        $this->page->setTitle(__('page.login'));

        $user = new User;
        if($user->isLogged()) {
            if($this->fetchGet('i') && Validator::length(8)->validate($this->fetchGet('i'))) {
                $invitation = \Modl\Invite::get($this->fetchGet('i'));
                $this->redirect('chat', [$invitation->resource, 'room']);
            } else {
                $this->redirect('root');
            }
        }
    }
}
