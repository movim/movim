<?php 

/**
 * \class User
 * \brief Handles the user's login and user.
 *
 */
class User {
	private static $instance;

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
			$this->username = $_SESSION['login'];
			$this->password = $_SESSION['pass'];

			$this->xmppSession = XMPPConnect::getInstance($this->username);
			$this->xmppSession->pingServer();
		}
		else if(isset($_POST['login'])
				&& isset($_POST['pass'])
				&& $_POST['login'] != ''
				&& $_POST['pass'] != '') {
			$this->authenticate($_POST['login'], $_POST['pass']);
		}
	}

	/**
	 * Checks if the user has an open session.
	 */
	function isLogged()
	{
		// User is not logged in if both the session vars and the members are unset.
		return (($this->username != '' && $this->password != '')
				|| (isset($_SESSION['login']) && ($_SESSION['login'] != '')));
	}

	function authenticate($login,$pass)
	{
		try{
			$this->xmppSession = XMPPConnect::getInstance($login);
			$this->xmppSession->login($login, $pass);

			$data = GetConf::getUserConf($login);

			// Careful guys, md5 is _not_ secure. SHA1 recommended here.
			if(sha1($pass) == $data['pass']) {
				$_SESSION['login'] = $login;
				$_SESSION['pass'] = $pass;

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
		unset($_SESSION['login']);
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

