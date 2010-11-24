<?php

class GetConf
{
	function __construct() {	
	}
	
	/* Return the element of the general configuration */
	
	static function getConf($element) {
		$data = @simplexml_load_file(BASE_PATH."/config/conf.xml");
		return $data->$element;
	}
	
	/* Return an array of the host configuration */
	
	static function getHostConf($jid) {
		$conf_file = BASE_PATH . "/user/$jid/conf.xml";
		
		return self::readConfFile($conf_file);
	}
	
	/* Return an array of the user configuration */
	
	static function getUserConf($jid) {
		$conf_file = BASE_PATH . "/user/$jid/data.xml";
		
		return self::readConfFile($conf_file);
	}
	
	/* Actually reads the XML file if it exists */
	
	static function readConfFile($file_path) {
		if(!file_exists($file_path)) {
			throw new MovimException(sprintf(_("Error: Cannot load file `%s'"), $file_path));
		}

		return simplexml_load_file($file_path);
	}

}
