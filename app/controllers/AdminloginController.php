<?php

use Movim\Controller\Base;
use App\Configuration;

class AdminloginController extends Base
{
    public function dispatch()
    {
        $this->page->setTitle(__('page.administration'));
        session_start();

        $configuration = Configuration::findOrNew(1);

        if (isset($_POST['username'])
        && $configuration->username == $_POST['username']
        && (password_verify($_POST['password'], $configuration->password)
            || $configuration->password == sha1($_POST['password']))) {
            $_SESSION['admin'] = true;
        }

        if (isset($_SESSION['admin']) && $_SESSION['admin'] == true) {
            $this->redirect('admin');
        }
    }
}
