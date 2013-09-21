<?php
namespace system;
if (!defined('DOCUMENT_ROOT')) die('Access denied');

class Conf
{
    public static $conf_path = "/config";
    public static $conf_files = array();

    /* Return the general configuration */

    static function getServerConf() {
        $conf_file = DOCUMENT_ROOT . self::$conf_path . "/conf.php";
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
            'timezone'  => 'Etc/GMT',
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

        require($file_path);
        return $conf;
    }
    
    static function saveConfFile($conf = array()) {
        $out = '<?php $conf = array(';
        
        foreach($conf as $key => $value) 
            $out .= "'".$key."' => '". $value . "',"."\n";
        $out .= ');';
        
        $fp = fopen(DOCUMENT_ROOT.self::$conf_path.'/conf.php', 'w');
        fwrite($fp, $out);
        fclose($fp);
    }

}
