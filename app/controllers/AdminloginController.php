<?php

class AdminloginController extends BaseController {
    function load() {
        $this->session_only = false;
    }

    function dispatch() {
        $this->page->setTitle(__('title.administration', APP_TITLE));

        $this->page->menuAddLink(__('page.home'), 'main');
        $this->page->menuAddLink(__('page.administration'), 'admin', true);
        
        $conf = Conf::getServerConf();
        
        if($conf['user'] == $_POST['username'] 
        && $conf['pass'] == sha1($_POST['password'])) {
            $_SESSION['admin'] = true;
            $this->name = 'admin';
        }
    }
}
