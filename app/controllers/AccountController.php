<?php

use Movim\Controller\Base;

class AccountController extends Base
{
    public function dispatch()
    {
        requestAPI('disconnect', 2, ['sid' => SESSION_ID]);

        $this->page->setTitle(__('page.account_creation'));
    }
}
