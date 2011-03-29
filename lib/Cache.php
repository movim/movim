<?php 

function movim_cache($key)
{
	$arglist = func_get_args();
	$key = $arglist[0];
	$content = $arglist[1];
	
	$user = new User();
	$login = $user->getLogin();
	
	if(!is_dir(BASE_PATH."/user/".$login."/cache"))
		mkdir(BASE_PATH."/user/".$login."/cache", 0755);
	
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
        }
	}
}

