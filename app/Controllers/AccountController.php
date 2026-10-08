<?php

namespace App\Controllers;

use App\Configuration;
use Movim\Controller\Base;

class AccountController extends Base
{
    public function dispatch()
    {
        if (Configuration::get()->disableregistration) {
            $this->redirect('login');
        }

        if ($this->user) {
            requestAPI('disconnect', post: ['sid' => $this->user->session->id]);
        }

        $this->page->setTitle(__('page.account_creation'));
    }
}
