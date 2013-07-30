<?php

/**
 * @file infos.php
 * This file is part of MOVIM.
 * 
 * @brief This PHP fiel create a light XML who sum up some information about the Movim pod
 *
 * @author edhelas <edhelas@movim.eu>
 *
 * @version 1.0
 * @date  26 march 2012
 *
 * Copyright (C)2010 MOVIM team
 * 
 * See the file `COPYING' for licensing information.
 */

// We load the Movim kernel
/**
* BOOTSTRAP
**/
define('ROOTDIR',  dirname(__FILE__));
require_once(ROOTDIR.'/system/Utils.php');
require_once(ROOTDIR.'/system/Conf.php');
try {
    define('ENVIRONMENT',Conf::getServerConfElement('environment'));
} catch (Exception $e) {
    define('ENVIRONMENT','production');//default environment is production
}
if (ENVIRONMENT === 'development') {
    ini_set('log_errors', 0);
    ini_set('display_errors', 1);
    ini_set('error_reporting', E_ALL ^ E_DEPRECATED ^ E_NOTICE);
} else {
    ini_set('log_errors', 1);
    ini_set('display_errors', 0);
    ini_set('error_reporting', E_ALL ^ E_DEPRECATED ^ E_NOTICE);
}
ini_set('error_log', ROOTDIR.'/log/php.log');

// Run
require_once('init.php');

  
// We get the informations
$pop = 0;

foreach(scandir(USERS_PATH) as $f)
    if(is_dir(USERS_PATH.'/'.$f))
        $pop++;
        
$pop = $pop-2;
  
// We create a simple DOMDocument
$doc = new DOMDocument("1.0");
$doc->formatOutput = true;

$infos = $doc->createElement("infos");
$doc->appendChild($infos);

    $language = $doc->createElement("language");
    $language->appendChild($doc->createTextNode($conf["defLang"]));
    $infos->appendChild($language);
    
    $population = $doc->createElement("population");
    $population->appendChild($doc->createTextNode($pop));
    $infos->appendChild($population);
    
    $whitelist = $doc->createElement("whitelist");
    $whitelist->appendChild($doc->createTextNode($conf["xmppWhiteList"]));
    $infos->appendChild($whitelist);

    $version = $doc->createElement("version");
    $version->appendChild($doc->createTextNode(APP_VERSION));
    $infos->appendChild($version);
    fclose($f);
    
    $phpversion = $doc->createElement("phpversion");
    $phpversion->appendChild($doc->createTextNode(phpversion()));
    $infos->appendChild($phpversion);

    $limit = $doc->createElement("userlimit");
    $limit->appendChild($doc->createTextNode($conf['maxUsers']));
    $infos->appendChild($limit);

// And we dispatch it !
ob_clean();
ob_start();
header("Content-type: text/plain");
echo $doc->saveXML();
