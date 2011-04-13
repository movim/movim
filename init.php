<?php

ob_start();
session_commit();

session_start();

define('BASE_PATH', dirname(__FILE__) . '/');
define('LIB_PATH',BASE_PATH.'system/');
define('PROPERTIES_PATH',BASE_PATH.'page/properties/');
define('THEMES_PATH', BASE_PATH . 'themes/');

require_once(LIB_PATH . 'Lang/i18n.php');
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

    /* Exceptions */
    $manual_load = '(^JAXL)'; // Put ORs as "(^foo|^bar|^baz)"

	if(preg_match('/'.$manual_load.'/', $className)) {
		return;
	}


    $file = "";

    /* This loads zend-like paths for libraries e.g.
     *
     *   Lib_Class_FooBar => ./Lib/Class/FooBar.php
	 *
	 * Note that the classes can be stored into subdirectories, in which case,
	 * the corresponding path is determined from the underscores in the
	 * classname. Thus, My_Nice_Class will be loaded from 'My/Nice/Class.php'.
	 */
    if(strpos($className, '_') !== false) {
		$tclass = explode('_', $className);

	  	$file = LIB_PATH.$tclass[0];

	  	for($i = 1; $i < sizeof($tclass); $i++) {
			$file .= "/".$tclass[$i];
		}
	}
    /* This is a new lib packaging process. The point here is that the library
     * sits at only one directory depth, which allows seemless integration and
     * proper class naming e.g.
     *
     *   LibFooBar => ./Lib/LibFooBar.php
     *
     * In which case, the class has the same name as the containing file.
     *
     * Note that this is limited to level 1. Thus loading class FooBarBaz will
     * search folder `Foo' for file FooBarBaz.php and load it. It will not load
     * the file in Foo/Bar/FooBarBaz.php.
     */
    else if(preg_match('/^[A-Z][a-z0-9_]+[A-Z][a-z0-9_]+/', $className)) { // Camelcase
        $tclass = preg_replace('/^([A-Z][a-z0-9_]+)[A-Z].+$/',
                               '$1', $className);

        $lib = LIB_PATH.$tclass;

        if(file_exists($lib) && is_dir($lib)) {
            $file = $lib.'/'.$className;
        } else {
            $tclass = explode('_', $className);

            $file = LIB_PATH.$tclass[0];

            for($i = 1; $i < sizeof($tclass); $i++) {
                $file .= "/". $tclass[$i];
            }
        }
    }
    else { /* Simple classes that sit straight in system/ */
        $file = LIB_PATH.$className;
    }

	$file .= ".php";

	if(file_exists($file)) {
		require_once($file);
	} else {
        echo "File for class $className: $file<br />";
		throw new MovimException(t("Erreur Autoload : le fichier %s n'existe pas", $file));
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
