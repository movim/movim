<?php

class AdminController extends BaseController {
    function load() {
        $this->session_only = false;
    }

    function dispatch() {
        session_start();
        
        if(isset($_SESSION['admin']) && $_SESSION['admin'] == true) {            
            $this->page->setTitle(__('title.administration', APP_TITLE));

            $this->page->menuAddLink(__('page.home'), 'root');
            $this->page->menuAddLink(__('page.administration'), 'admin', true);
        } else {
            $this->name = 'adminlogin';
        }

        //session_write_close();
    }
}
