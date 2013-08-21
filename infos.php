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
define('DOCUMENT_ROOT', dirname(__FILE__));
require_once(DOCUMENT_ROOT.'/bootstrap.php');

$bootstrap = new Bootstrap();
$booted = $bootstrap->boot();

  
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
