<?php
use Movim\Controller\Base;

class AccountController extends Base
{
    function load()
    {
        $this->session_only = false;
    }

    function dispatch()
    {
        requestURL('http://localhost:1560/disconnect/', 2, ['sid' => SESSION_ID]);

        $this->page->setTitle(__('page.account_creation'));
    }
}
