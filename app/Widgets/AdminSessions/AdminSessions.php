<?php

namespace App\Widgets\AdminSessions;

use App\Contact;
use App\Session;
use App\User;
use Movim\Widget\Base;

class AdminSessions extends Base
{
    public function getContact(User $user)
    {
        return Contact::firstOrNew(['id' => $user->id]);
    }

    public function display()
    {
        $this->view->assign('sessions', Session::with(['user', 'presence'])->get());
    }
}
