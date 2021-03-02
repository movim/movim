<?php

use Movim\Controller\Base;

class AccountnextController extends Base
{
    public function dispatch()
    {
        if (\App\Configuration::get()->disableregistration) {
            $this->redirect('login');
        }

        $this->page->setTitle(__('page.account_creation'));
    }
}
