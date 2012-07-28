<?php

class Conf
{
    public static $conf_path = "/config";
    public static $conf_files = array();

	function __construct() {

	}

	/* Return the general configuration */

	static function getServerConf() {
		$conf_file = BASE_PATH . self::$conf_path . "/conf.xml";
        return self::getConf('server', $conf_file);
	}

    /* Gets a configuration. */
    static function getConf($name, $path)
    {
        if(file_exists($path)) {
            if(!array_key_exists($name, self::$conf_files)) {
                self::$conf_files[$name] = self::readConfFile($path);
            }
            return self::$conf_files[$name];
        } else {
            return false;
        }
    }

	/* Return the element of the general configuration */

	static function getServerConfElement($element) {
		$conf = self::getServerConf();

		if(!isset($conf[$element])) {
			throw new MovimException(t("Cannot load element value '%s'", $element));
		}
		else {
			return $conf[$element];
		}
	}

	/* Actually reads the XML file if it exists */

	static function readConfFile($file_path) {
		if(!file_exists($file_path)) {
			throw new MovimException(t("Cannot load file '%s'", $file_path));
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
    static private function convertXmlObjToArr( $obj, &$arr = null)
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
