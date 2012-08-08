<?Php

/**
 * \class User
 * \brief Handles the user's login and user.
 *
 */
class User {
	private $xmppSession;

	private $username = '';
	private $password = '';

	/**
	 * Class constructor. Reloads the user's session or attempts to authenticate
	 * the user.
	 * Note that the constructor is private. This class is a singleton.
	 */
	function __construct()
	{
		if($this->isLogged()) {
            global $session;
            
            //$sess = Session::start(APP_NAME);
			$this->username = $session['user'].'@'.$session['host'];
			//$this->password = $sess->get('pass');

			/*$this->xmppSession = Jabber::getInstance($this->username);*/
		}
		/*else if(isset($_POST['login'])
				&& isset($_POST['pass'])
				&& $_POST['login'] != ''
				&& $_POST['pass'] != '') {
			$this->authenticate($_POST['login'], $_POST['pass']);
		}*/
	}

	/**
	 * Checks if the user has an open session.
	 */
	function isLogged()
	{
		// User is not logged in if both the session vars and the members are unset.
        //$sess = Session::start(APP_NAME);
		//return (($this->username != '' && $this->password != '') || $sess->get('login'));
        global $session;
        return $session['on'];
	}

	/*function authenticate($login,$pass)
	{
		try{

            $data = UserConf::getConf($login);
            if( $data == false ) {
			    // We check if we wants to create an account
                header('Location:'.BASE_URI.'index.php?q=disconnect&err=noaccount');
                exit;
            }


			// Careful guys, md5 is _not_ secure. SHA1 recommended here.
			if(sha1($pass) == $data['pass']) {				
                $sess = Session::start(APP_NAME);
 
                $sess->set('login', $login);
                $sess->set('pass', $pass);
                
                $this->username = $login;
				$this->password = $pass;

				$this->xmppSession = Jabber::getInstance($login);
				$this->xmppSession->login($login, $pass);
			} else {
				header('Location:'.BASE_URI.'index.php?q=disconnect&err=wrongpass');
                exit;
			}
		}
		catch(MovimException $e){
			echo $e->getMessage();
            
            // If we've got an error on a new account
            if($e->getCode() == 300)
            {
                global $sdb;
                $conf = new ConfVar();
				$sdb->load($conf, array(
									'login' => $this->getLogin()
										));
                if($conf->get('first') == 0)
                    $conf->set('first', 2);
				$sdb->save($conf);	
                header('Location:'.BASE_URI.'index.php?q=disconnect&err=wrongaccount');
                exit;
            }
			return $e->getMessage();
		}
	}*/

	function desauth()
	{
        PresenceHandler::clearPresence();

    //    $sess = Session::start('jaxl');
    //    Session::dispose('jaxl');
    
        $p = new moxl\PresenceUnavaiable();
        $p->request();

        $sess = Session::start(APP_NAME);
        Session::dispose(APP_NAME);
	}

    function setLang($language)
    {
        global $sdb;
        $conf = $sdb->select('ConfVar', array('login' => $this->username));
        $conf[0]->set('language', $language);
        $sdb->save($conf[0]);
    }

	function getLogin()
	{
		return $this->username;
	}

	function getPass()
	{
		return $this->password;
	}

}

