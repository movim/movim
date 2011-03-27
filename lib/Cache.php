<?php 

function movim_cache($key)
{
	$arglist = func_get_args();
	$key = $arglist[0];
	$content = $arglist[1];
	
	$user = new User();
	$login = $user->getLogin();
	
	if(!is_dir(BASE_PATH."/user/".$login."/cache"))
		mkdir(BASE_PATH."/user/".$login."/cache", 0766);
	
	if(func_num_args() == 1) {
		if(file_exists(BASE_PATH."/user/".$login."/cache/".$key)) {
			$content = unserialize(file_get_contents(BASE_PATH."/user/".$login."/cache/".$key));
		}

		if(isset($content) && $content != "")
			return $content;
		else
			return "";
	}
	
	if(func_num_args() == 2) {
        if(!file_put_contents(BASE_PATH."/user/".$login."/cache/".$key, serialize($content))) {
            throw new MovimException(sprintf(t("Couldn't set cache file %s"), $key));
        }/* else {
        	file_put_contents(BASE_PATH."/user/".$login."/".$key, $content);
        }*/
	}
}
#doc
#	classname:	Cache
#	scope:		PUBLIC
#
#/doc

/*class Cache 
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

}*/
###
