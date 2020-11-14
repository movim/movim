<?php

use Movim\Controller\Base;

class AdminController extends Base
{
    public function dispatch()
    {
        session_start();

        if (isset($_SESSION['admin']) && $_SESSION['admin'] == true) {
            $this->page->setTitle(__('page.administration'));
        } else {
            $this->redirect('adminlogin');
        }
    }
}
