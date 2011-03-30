<?php
ob_start();
session_commit();

session_start();

define('BASE_PATH', dirname(__FILE__) . '/');
define('LIB_PATH',BASE_PATH.'lib/');
define('PROPERTIES_PATH',BASE_PATH.'page/properties/');
define('THEMES_PATH', BASE_PATH . 'themes/');

require_once(LIB_PATH . 'i18n.php');
require_once(LIB_PATH . 'Utils.php');
require_once(LIB_PATH . 'Cache.php');

define('APP_TITLE', t("MOVIM - Test Client"));

{
	$index_pos = strpos($_SERVER['PHP_SELF'], 'index.php');
	$path = "";
	if($index_pos <= 0) {
		$path = $_SERVER['PHP_SELF'];
	} else {
		$path = substr($_SERVER['PHP_SELF'], 0, $index_pos);
	}
	$uri = "";
	if($path == "") {
		$uri = 'http://' . $_SERVER['HTTP_HOST'] . '/';
	} else {
		$uri = 'http://' . $_SERVER['HTTP_HOST'] . $path;
	}
	
	define('BASE_URI', $uri);
}

/**
 * This automatically loads up class definitions when encountered by PHP. It is
 * a magic function. Please refer to PHP's documentation if you have trouble
 * with this.
 */
function __autoload($className) {
	
	if(preg_match('/^JAXL/', $className)) {
		return;
	}

	/* Else we load the default lib path.
	 *
	 * Note that the classes can be stored into subdirectories, in which case,
	 * the corresponding path is determined from the underscores in the
	 * classname. Thus, My_Nice_Class will be loaded from 'My/Nice/Class.php'.
	 */
    else if(preg_match('/^[A-Z][a-z0-9_]+[A-Z][a-z0-9_]+$/', $className)) { // Camelcase
        $tclass = preg_replace('/^([A-Z][a-z0-9_]+[A-Z][a-z0-9_]+$/',
                               '$1', $className);

        $lib = LIB_PATH.$tclass
        
        if(file_exists($lib) && is_dir($lib)) {
            $file = $lib.'/'.$className;
        } else {
            return;
        }
    }
	else {
		$tclass = explode('_', $className);

	  	$file = LIB_PATH.$tclass[0];
		
	  	for($i = 1; $i < sizeof($tclass); $i++) {
			$file .= "/{$tclass[$i]}";
		}
	}
	  
	$file .= ".php";
  
	if(file_exists($file)) {
		require_once($file);
	} else {
		throw new MovimException(t("Erreur Autoload : le fichier {$file} n'existe pas"));
	}
}

function movim_log($log) {
	ob_start();
	var_dump($log);
	$dump = ob_get_clean();
	$fh = fopen(BASE_PATH . 'log/movim.log', 'w');
	fwrite($fh, $dump);
	fclose($fh);
}

?>
