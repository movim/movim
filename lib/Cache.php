<?php 

#doc
#	classname:	Cache
#	scope:		PUBLIC
#
#/doc

class Cache 
{
	#	internal variables
	private $_login;
	#	Constructor
	function __construct ()
	{
		# code...
		$session = Session::getInstance();;
		$this->_login = $session->getLogin();
	}
	###	
	public function get()
	{
		if(file_exists(BASE_PATH."/user/".$this->_login."/cache.tmp")) {
			$content = file_get_contents(BASE_PATH."/user/".$this->_login."/cache.tmp");
		}
		else {
			$content = "User cache not found";
		}
		
		return $content;	
	}
	
	public function put($html)
	{
		if(file_exists(BASE_PATH."/user/".$this->_login."/cache.tmp")) {
			$content = file_get_contents(BASE_PATH."/user/".$this->_login."/cache.tmp");
			$content = $html."\n".$content;
			$inF = fopen(BASE_PATH."/user/".$this->_login."/cache.tmp","w");
			fwrite($inF,$content);
			fclose($inF);
		}
		else {
			$content = "User cache not found";
		}
	}

}
###
