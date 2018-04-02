<?php

class Statistics extends \Movim\Widget\Base
{
    public function getContact(\App\User $user)
    {
        return \App\Contact::firstOrNew(['id' => $user->id]);
    }

    function display()
    {
        $this->view->assign('sessions', \App\Session::get());
    }
}
