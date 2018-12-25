<?php

use Movim\Controller\Base;

class AccountnextController extends Base
{
    public function load()
    {
        $this->session_only = false;
    }

    public function dispatch()
    {
        $this->page->setTitle(__('page.account_creation'));
    }
}
