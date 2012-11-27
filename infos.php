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
require("init.php");
  
// We get the informations
$conf = Conf::getServerConf();
global $sdb;
$contacts = $sdb->select('CacheVar', array());
  
// We create a simple DOMDocument
$doc = new DOMDocument("1.0");
$doc->formatOutput = true;

$infos = $doc->createElement("infos");
$doc->appendChild($infos);

    $language = $doc->createElement("language");
    $language->appendChild($doc->createTextNode($conf["defLang"]));
    $infos->appendChild($language);
    
    $population = $doc->createElement("population");
    $population->appendChild($doc->createTextNode(ceil(count($contacts)/2)));
    $infos->appendChild($population);
    
    $whitelist = $doc->createElement("whitelist");
    $whitelist->appendChild($doc->createTextNode($conf["xmppWhiteList"]));
    $infos->appendChild($whitelist);

    $file = "VERSION";
    if($f = fopen($file, 'r')){
        $version = $doc->createElement("version");
        $version->appendChild($doc->createTextNode(trim(fgets($f))));
        $infos->appendChild($version);
        fclose($f);
    }
    
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
