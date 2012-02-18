<?php

class ConfVar extends StorageBase {
    protected $login;
    protected $pass;

    protected $host;
    protected $domain;
    protected $port;

    protected $boshHost;
    protected $boshSuffix;
    protected $boshPort;

    protected $language;

    protected $first;

    protected function type_init() {
        $this->login      = StorageType::varchar(128);
        $this->pass       = StorageType::varchar(128);

        $this->host       = StorageType::varchar(128);
        $this->domain     = StorageType::varchar(128);
        $this->port       = StorageType::int();

        $this->boshHost   = StorageType::varchar(128);
        $this->boshSuffix = StorageType::varchar(128);
        $this->boshPort   = StorageType::int();

        $this->language   = StorageType::varchar(128);

        $this->first      = StorageType::int();
    }

    public function setConf(
                            $login = false,
                            $pass = false,
                            $host = false,
                            $domain = false,
                            $port = false,
                            $boshhost = false,
                            $boshsuffix = false,
                            $boshport = false,
                            $language = false,
                            $first = false
                           ) {

        list($user, $host) = explode('@', $login);

        $this->login->setval(($login != false) ? $login : $this->login->getval());
        $this->pass->setval(($pass != false) ? sha1($pass) : $this->pass->getval());

        $this->host->setval(($host != false) ? $host : $this->host->getval());
        $this->domain->setval(($host != false) ? $host : $this->domain->getval());
        $this->port->setval(5222);

        $this->boshHost->setval(($boshhost != false) ? $boshhost : $this->boshHost->getval());
        $this->boshSuffix->setval(($boshsuffix != false) ? $boshsuffix : $this->boshSuffix->getval());
        $this->boshPort->setval(($boshport != false) ? $boshport : $this->boshPort->getval());

        $this->language->setval(($language != false) ? $language : $this->language->getval());

        if($first) $this->first->setval(1);

    }

    public function getConf() {
        $array = array();
        $array['login'] = $this->login->getval();
        $array['pass'] = $this->pass->getval();

        $array['host'] = $this->host->getval();
        $array['domain'] = $this->domain->getval();
        $array['port'] = $this->port->getval();

        $array['boshHost'] = $this->boshHost->getval();
        $array['boshSuffix'] = $this->boshSuffix->getval();
        $array['boshPort'] = $this->boshPort->getval();

        $array['language'] = $this->language->getval();

        $array['first'] = $this->first->getval();

        return $array;
    }

}

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
			throw new MovimException(t("Cannot load element value'%s'", $element));
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
