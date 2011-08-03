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
            $sess = Session::start(APP_NAME);
			$this->username = $sess->get('login');
			$this->password = $sess->get('pass');

			$this->xmppSession = Jabber::getInstance($this->username);
		}
		else if(isset($_POST['login'])
				&& isset($_POST['pass'])
				&& $_POST['login'] != ''
				&& $_POST['pass'] != '') {
			$this->authenticate($_POST['login'], $_POST['pass'], $_POST['host'], $_POST['suffix'], $_POST['port'], $_POST['create']);
		}
	}

	/**
	 * Checks if the user has an open session.
	 */
	function isLogged()
	{
		// User is not logged in if both the session vars and the members are unset.
        $sess = Session::start(APP_NAME);
		return (($this->username != '' && $this->password != '') || $sess->get('login'));
	}

	function authenticate($login,$pass, $boshhost, $boshsuffix, $boshport, $create)
	{
		try{
		    // We check the JID
		    if(filter_var($login, FILTER_VALIDATE_EMAIL) == false) {
                header('Location:'.BASE_URI.'index.php?q=disconnect&err=invalidjid');
                exit();
            }
            
            $data = false;
			if( !($data = Conf::getUserData($login))) {
			    // We check if we wants to create an account
			    if($create == "on") {
			        // We check the BOSH URL if we create a new account
			        $ch = curl_init('http://'.$boshhost.':'.$boshport.'/'.$boshsuffix.'/');
                    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
                    curl_exec($ch);
                    $errno = curl_errno($ch);
                    curl_close($ch);
                    
			        if($errno != 0) {
			            header('Location:'.BASE_URI.'index.php?q=disconnect&err=bosherror');
                        exit();
			        } else {
                        Conf::createUserConf($login, $pass, $boshhost, $boshsuffix, $boshport);
                        $data = Conf::getUserData($login);
                    }
                } else {
                    header('Location:'.BASE_URI.'index.php?q=disconnect&err=noaccount');   
                }
            }

			$this->xmppSession = Jabber::getInstance($login);
			$this->xmppSession->login($login, $pass);

			// Careful guys, md5 is _not_ secure. SHA1 recommended here.
			if(sha1($pass) == $data['pass']) {
                $sess = Session::start(APP_NAME);
                $sess->set('login', $login);
                $sess->set('pass', $pass);

				$this->username = $login;
				$this->password = $pass;
			} else {
				throw new MovimException(t("Wrong password"));
			}
		}
		catch(MovimException $e){
			echo $e->getMessage();
			return $e->getMessage();
		}
	}

	function desauth()
	{
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

}

