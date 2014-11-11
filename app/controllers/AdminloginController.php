<?php

class AdminloginController extends BaseController
{
    function load()
    {
        $this->session_only = false;
    }

    function dispatch()
    {
        $this->page->setTitle(__('title.administration', APP_TITLE));

        $this->page->menuAddLink(__('page.home'), 'root');
        $this->page->menuAddLink(__('page.administration'), 'admin', true);
        
        $cd = new \Modl\ConfigDAO();
        $config = $cd->get();
        
        if($config->username == $_POST['username'] 
        && $config->password == sha1($_POST['password'])) {
            $_SESSION['admin'] = true;
            $this->name = 'admin';
        }
    }
}
