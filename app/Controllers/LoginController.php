<?php

namespace App\Controllers;

use App\Invite;
use Movim\Controller\Base;
use Respect\Validation\Validator;

class LoginController extends Base
{
    public function dispatch()
    {
        $this->page->setTitle(__('page.login'));

        if ($this->user) {
            if ($this->fetchGet('i') && Validator::length(8)->isValid($this->fetchGet('i'))) {
                $invitation = Invite::find($this->fetchGet('i'));

                if (! $invitation) {
                    $this->redirect('main');
                }

                $this->redirect('chat', [$invitation->resource, 'room']);
            } else {
                $this->redirect('main');
            }
        }
    }
}
