<?php

class Conf
{
    public static $conf_path = "/config";
    public static $conf_files = array();

    /* Return the general configuration */

    static function getServerConf() {
        $conf_file = DOCUMENT_ROOT . self::$conf_path . "/conf.xml";
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
            // Return the default configuration
            return self::getDefault();
        }
    }
    
    static function getDefault() {
        return array(
            'environment' => 'development',//allow "production" and "development" for the moment
            'theme'     => 'movim',
            'defLang'   => 'en',
            'maxUsers'  => -1,
            'logLevel'  => 7,
            'timezone'  => getLocalTimezone(),
            'dbType'    => 'mysql',
            'dbUsername'=> 'username',
            'dbPassword'=> 'password',
            'dbHost'    => 'localhost',
            'dbPort'    => '3306',
            'dbName'    => 'movim',
            'boshUrl'   => 'http://localhost:5280/http-bind',
            'xmppWhiteList' => '',
            'info'      => '',
            'user'      => 'admin',
            'pass'      => '5baa61e4c9b93f3f0682250b6cf8331b7ee68fd8',
            'sizeLimit' => 20240001);
    }

    /* Return the element of the general configuration */

    static function getServerConfElement($element) {
        $conf = self::getServerConf();
        if(!isset($conf[$element])) {
            $conf = self::getDefault();
            return $conf[$element];
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
    
    static function saveConfFile($conf = array()) {
        movim_log($conf);
        $doc = new DOMDocument('1.0', 'UTF-8');

        $doc->formatOutput = true;

        $config = $doc->createElement("config");
        $doc->appendChild($config);
        
        foreach($conf as $key => $value) {            
            $node = $doc->createElement($key);
            $node->appendChild($doc->createTextNode($value));
            $config->appendChild($node);
        }
        
        $xml = $doc->saveXML();
        file_put_contents(DOCUMENT_ROOT.self::$conf_path.'/conf.xml', $xml);
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
