<?php

class AdminController extends BaseController {
    function load() {
        $this->session_only = false;
    }

    function dispatch() {
        session_start();
        
        if(isset($_SESSION['admin']) && $_SESSION['admin'] == true) {            
            $this->page->setTitle(__('title.administration', APP_TITLE));
        } else {
            $this->name = 'adminlogin';
        }

        //session_write_close();
    }
}
