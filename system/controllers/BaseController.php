<?php

class BaseController {
    public $name = 'main';   // The name of the current page
    protected $session_only = false;// The page is protected by a session ?
    protected $raw = false;			// Display only the content ?
    protected $page;

    function __construct() {
        $this->load_language();
        $this->page = new TplPageBuilder();
        $this->page->addScript('movim_hash.js');
        $this->page->addScript('movim_utils.js');
        $this->page->addScript('movim_base.js');
        $this->page->addScript('movim_tpl.js');
        $this->page->addScript('movim_rpc.js');
    }


    /**
     * Loads up the language, either from the User or default.
     */
    function load_language() {
        $user = new user();
        if($user->isLogged()) {
            try{
                $lang = $user->getConfig('language');
                load_language($lang);
            }
            catch(MovimException $e) {
                // Load default language.
                load_language(\system\Conf::getServerConfElement('defLang'));
            }
        }
        else if(isset($_SERVER['HTTP_ACCEPT_LANGUAGE'])) {
            load_language_auto();
        }
        else {
            load_language(\system\Conf::getServerConfElement('defLang'));
        }
    }

    /**
     * Returns the value of a $_GET variable. Mainly used to avoid getting
     * notices from PHP when attempting to fetch an empty variable.
     * @param name is the desired variable's name.
     * @return the value of the requested variable, or FALSE.
     */
    protected function fetch_get($name)
    {
        if(isset($_GET[$name])) {
            return htmlentities($_GET[$name]);
        } else {
            return false;
        }
    }

    /**
     * Returns the value of a $_POST variable. Mainly used to avoid getting
     * notices from PHP when attempting to fetch an empty variable.
     * @param name is the desired variable's name.
     * @return the value of the requested variable, or FALSE.
     */
    protected function fetch_post($name)
    {
        if(isset($_POST[$name])) {
            return htmlentities($_POST[$name]);
        } else {
            return false;
        }
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
        
        if($this->raw) {
			echo $content->build($this->name.'.tpl');
			exit;
        } else {
			$built = $content->build($this->name.'.tpl');
			$this->page->setContent($built);
			echo $this->page->build('page.tpl');
		}
    }
}
