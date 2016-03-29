<?php
use Movim\Controller\Base;

class AdminController extends Base {
    function load() {
        $this->session_only = false;
    }

    function dispatch() {
        session_start();

        if(isset($_SESSION['admin']) && $_SESSION['admin'] == true) {
            $this->page->setTitle(__('page.administration'));
        } else {
            $this->name = 'adminlogin';
        }
    }
}
