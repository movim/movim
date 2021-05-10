<?php

use Movim\Widget\Base;

class AdminSessions extends Base
{
    public function getContact(\App\User $user)
    {
        return \App\Contact::firstOrNew(['id' => $user->id]);
    }

    public function display()
    {
        $this->view->assign('sessions', \App\Session::with('user')->get());
    }
}
