<?php

class BaseController {
    public $name = 'main';   // The name of the current page
    protected $session_only = false;// The page is protected by a session ?
    protected $page;

    function __construct() {
        $this->page = new TplPageBuilder();
        $this->page->addScript('movim_hash.js');
        $this->page->addScript('movim_utils.js');
        $this->page->addScript('movim_base.js');
        $this->page->addScript('movim_tpl.js');
        $this->page->addScript('movim_rpc.js');
    }

    function check_session() {
        if($this->session_only) {
            $user = new User();

            if(!$user->isLogged()) {
                $this->name = 'login';
            }
        }
    }

    function display() {
        if($this->session_only) {
            $user = new User();
            $content = new TplPageBuilder($user);
        } else {
            $content = new TplPageBuilder();
        }
        
        $built = $content->build($this->name.'.tpl');
        //$this->page->setContent($built);
        //echo $this->page->build('page.tpl');
    }
}
