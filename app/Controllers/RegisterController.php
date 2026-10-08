<?php

namespace App\Controllers;

use App\Configuration;
use Movim\Controller\Base;

class RegisterController extends Base
{
    public function dispatch()
    {
        if (Configuration::get()->disableregistration) {
            $this->redirect('login');
        }

        $this->page->setTitle(__('page.account_creation'));
    }
}
