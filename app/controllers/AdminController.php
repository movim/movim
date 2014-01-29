<?php

class AdminController extends BaseController {
    function load() {
        $this->session_only = false;
    }

    function dispatch() {
        session_start();
        
        if(isset($_SESSION['admin']) && $_SESSION['admin'] == true) {            
            $this->page->setTitle(t('%s - Administration Panel', APP_TITLE));

            $this->page->menuAddLink(t('Home'), 'main');
            $this->page->menuAddLink(t('Administration'), 'admin', true);
        } else {
            $this->name = 'adminlogin';
        }

        session_write_close();
    }
}
