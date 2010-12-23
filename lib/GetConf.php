<?php

class GetConf
{
	function __construct() {	
	}
	
	/* Return the general configuration */
	
	static function getServerConf() {
		$conf_file = BASE_PATH . "/config/conf.xml";
		return self::readConfFile($conf_file);
	}
	
	/* Return the element of the general configuration */
	
	static function getServerConfElement($element) {
		$conf_file = BASE_PATH . "/config/conf.xml";
		$conf = self::readConfFile($conf_file);
		if(!isset($conf[$element])) {
			throw new MovimException(sprintf(t("Error: Cannot load element value'%s'"), $element));
		}
		else {
			return $conf[$element];
		}
	}
	
	/* Return an array of the host configuration */
	
	static function getUserConf($jid) {
		$conf_file = BASE_PATH . "/user/$jid/conf.xml";
		return self::readConfFile($conf_file);
	}
	
	/* Return an element of the host configuration */
	
	static function getUserConfElement($jid, $element) {
		$conf_file = BASE_PATH . "/user/$jid/conf.xml";
		$conf =  self::readConfFile($conf_file);
		if(!isset($conf[$element])) {
			throw new MovimException(sprintf(t("Error: Cannot load element value'%s'"), $element));
		}
		else {
			return $conf[$element];
		}
	}
	
	/* Return an array of the user configuration */
	
	static function getUserData($jid) {
		$conf_file = BASE_PATH . "/user/$jid/data.xml";
		return self::readConfFile($conf_file);
	}
	
	/* Actually reads the XML file if it exists */
	
	static function readConfFile($file_path) {
		if(!file_exists($file_path)) {
			throw new MovimException(sprintf(t("Error: Cannot load file '%s'"), $file_path));
		}

		$file = simplexml_load_file($file_path);
		$arr = array(); 
		self::convertXmlObjToArr( $file , $arr );
		return $arr;
	}
	
	/**
    * Parse a SimpleXMLElement object recursively into an Array.
    * Attention: attributes skipped
    *
    *
    * @param $xml The SimpleXMLElement object
    * @param $arr Target array where the values will be stored
    * @return NULL
    */
    private function convertXmlObjToArr( $obj, &$arr = null)
    {
        $children = $obj->children();
        $executed = false;
        foreach ($children as $elementName => $node)
        {
            if( array_key_exists( $elementName , $arr ) )
            {
                if(array_key_exists( 0 ,$arr[$elementName] ) )
                {
                    $i = count($arr[$elementName]);
                    self::convertXmlObjToArr ($node, $arr[$elementName][$i]);    
                }
                else
                {
                    $tmp = $arr[$elementName];
                    $arr[$elementName] = array();
                    $arr[$elementName][0] = $tmp;
                    $i = count($arr[$elementName]);
                    self::convertXmlObjToArr($node, $arr[$elementName][$i]);
                }
            }
            else
            {
                $arr[$elementName] = array();
                self::convertXmlObjToArr($node, $arr[$elementName]);   
            }
            $executed = true;
        }
        if(!$executed&&$children->getName()=="")
        {
            $arr = (String)$obj;
        }
        
        return ;
    }

}
