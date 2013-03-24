<?Php

/**
 * \class User
 * \brief Handles the user's login and user.
 *
 */
class User {
	private $xmppSession;

	public $username = '';
	private $password = '';
    private $config = array();
    
    public $userdir;
    public $useruri;
    
    public $sizelimit;

	/**
	 * Class constructor. Reloads the user's session or attempts to authenticate
	 * the user.
	 * Note that the constructor is private. This class is a singleton.
	 */
	function __construct()
	{
		if($this->isLogged()) {
            global $session;
			$this->username = $session['user'].'@'.$session['host'];
            $this->config = $session['config'];

            $this->sizelimit = (int)Conf::getServerConfElement('sizeLimit');

            $this->userdir = BASE_PATH.'users/'.$this->username.'/';
            $this->useruri = BASE_URI.'users/'.$this->username.'/';
        }
	}
    
    /**
     * Get the current size in bytes of the user directory
     */
    function dirSize()
    {
        $sum = 0;
        
        foreach(scandir($this->userdir) as $s) {
            if($s != '.' && $s != '..' && $s != 'index.html')
                $sum = $sum + filesize($this->userdir.$s);
        }
        
        return $sum;
    }

	/**
	 * Checks if the user has an open session.
	 */
	function isLogged()
	{
		// User is not logged in if both the session vars and the members are unset.
        global $session;
        return $session['on'];
	}

	function desauth()
	{
        $pd = new modl\PresenceDAO();
        $pd->clearPresence();

        if($this->isLogged()) {
            $p = new moxl\PresenceUnavaiable();
            $p->request();
        }

        $sess = Session::start(APP_NAME);
        Session::dispose(APP_NAME);
	}

	function getLogin()
	{
		return $this->username;
	}

	function getPass()
	{
		return $this->password;
	}

    function setConfig(array $config)
    {
        global $session;
        $session['config'] = $config;

        $sess = Session::start(APP_NAME);
        $sess->set('session', $session);
    }

    function getConfig($key = false)
    {
        if($key == false)
            return $this->config;
        if(isset($this->config[$key]))
            return $this->config[$key];
    }

}
