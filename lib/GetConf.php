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
		$conf = self::getServerConf();

		if(!isset($conf[$element])) {
			throw new MovimException(t("Error: Cannot load element value'%s'", $element));
		}
		else {
			return $conf[$element];
		}
	}

	/* Return an array of the host configuration */

	static function getUserConf($jid) {
		$conf_file = BASE_PATH . "/user/$jid/conf.xml";
        if(file_exists($conf_file)) {
            return self::readConfFile($conf_file);
        } else { // Creating default conf.
            return false;
        }
	}

	/* Return an element of the host configuration */

	static function getUserConfElement($jid, $element) {
        $conf = self::getUserConf($jid);

		if(!isset($conf[$element])) {
			throw new MovimException(t("Error: Cannot load element value'%s'", $element));
		}
		else {
			return $conf[$element];
		}
	}

	/* Set de new user configuration */

	static function setUserConf($jid, $new) {

		// We get the old configuration
		$old = self::getUserConf($jid);

		$conf = array();

		// We update only the new elements in the configuration
		foreach($old as $key => $value) {
			if($new[$key] != $old[$key] && isset($new[$key]))
				$conf[$key] = $new[$key];
			else
				$conf[$key] = $old[$key];
		}

		// And finally we wrote the new configuration
		$dir_conf = BASE_PATH . "/user/$jid";

		$conf_xml =
                '<?xml version="1.0" encoding="UTF-8"?>'."\n".
                '<data>'."\n";

        foreach($conf as $key => $value) {
        	$conf_xml .= "\t" . '<' . $key . '>' . $value . '</' . $key . '>' . "\n";
        }

        $conf_xml .= '</data>';

        if(!file_put_contents($dir_conf . "/conf.xml", $conf_xml))
            throw new MovimException(t("Couldn't create file %s", 'conf.xml'));
	}

	/* Return an array of the user configuration */

	static function getUserData($jid) {
		$conf_file = BASE_PATH . "/user/$jid/data.xml";

        if(file_exists($conf_file)) {
            return self::readConfFile($conf_file);
        } else { // Creating default conf.
            return false;
        }
	}

    static function createUserConf($jid, $password)
    {
        $dir_conf = BASE_PATH . "/user/$jid";

        if(!file_exists($dir_conf)) {
            // Splitting jid.
            list($user, $host) = explode('@', $jid);

            $serv = self::getServerConf();

            mkdir($dir_conf);
            $conf_xml =
                '<?xml version="1.0" encoding="UTF-8"?>'."\n".
                '<data>'."\n".
                '  <host>'.$host.'</host>'."\n".
                '  <domain>'.$host.'</domain>'."\n".
                '  <port>5222</port>'."\n".
                '  <boshHost>'.$serv['defBoshHost'].'</boshHost>'."\n".
                '  <boshSuffix>'.$serv['defBoshSuffix'].'</boshSuffix>'."\n".
                '  <boshPort>'.$serv['defBoshPort'].'</boshPort>'."\n".
                '  <language>'.$serv['defLang'].'</language>'."\n".
                '</data>';

            $data_xml =
                '<?xml version="1.0" encoding="UTF-8"?>'."\n".
                '<data>'."\n".
                '  <login>'.$jid.'</login>'."\n".
                '  <pass>'.sha1($password).'</pass>'."\n".
                '</data>';

            if(!file_put_contents($dir_conf . "/conf.xml", $conf_xml))
                throw new MovimException(t("Couldn't create file %s", 'conf.xml'));
            if(!file_put_contents($dir_conf . "/data.xml", $data_xml))
                throw new MovimException(t("Couldn't create file %s", 'data.xml'));
        } else {
            throw new MovimException(t("Couldn't create configuration files."));
        }
    }

	/* Actually reads the XML file if it exists */

	static function readConfFile($file_path) {
		if(!file_exists($file_path)) {
			throw new MovimException(t("Error: Cannot load file '%s'", $file_path));
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
