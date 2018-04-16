<?php

use Movim\Controller\Base;
use App\Configuration;

class AdminloginController extends Base
{
    function load()
    {
        $this->session_only = false;
    }

    function dispatch()
    {
        $this->page->setTitle(__('page.administration'));

        $configuration = Configuration::findOrNew(1);

        if (isset($_POST['username'])
        && $configuration->username == $_POST['username']
        && (password_verify($_POST['password'], $configuration->password)
            || $configuration->password == sha1($_POST['password']))) {
            $_SESSION['admin'] = true;
            $this->name = 'admin';
        }
    }
}
