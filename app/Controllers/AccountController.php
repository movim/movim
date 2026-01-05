<?php

namespace App\Controllers;

use Movim\Controller\Base;

class AccountController extends Base
{
    public function dispatch()
    {
        if (\App\Configuration::get()->disableregistration) {
            $this->redirect('login');
        }

        if ($this->user) {
            requestAPI('disconnect', post: ['sid' => $this->user->session->id]);
        }

        $this->page->setTitle(__('page.account_creation'));
    }
}
