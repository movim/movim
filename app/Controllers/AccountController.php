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

        requestAPI('disconnect', post: ['sid' => SESSION_ID]);

        $this->page->setTitle(__('page.account_creation'));
    }
}
