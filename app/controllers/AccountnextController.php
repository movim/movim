<?php

use Movim\Controller\Base;

class AccountnextController extends Base
{
    public function dispatch()
    {
        $this->page->setTitle(__('page.account_creation'));
    }
}
