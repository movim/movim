<?php

class AdminloginController extends BaseController {
    function load() {
        $this->session_only = false;
    }

    function dispatch() {
        $this->page->setTitle(t('%s - Administration Panel', APP_TITLE));

        $this->page->menuAddLink(t('Home'), 'main');
        $this->page->menuAddLink(t('Administration'), 'admin', true);
        
        $conf = Conf::getServerConf();
        
        if($conf['user'] == $_POST['username'] 
        && $conf['pass'] == sha1($_POST['password'])) {
            $_SESSION['admin'] = true;
            $this->name = 'admin';
        }
    }
}
