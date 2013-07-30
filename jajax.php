<?php

/**
 * @file jajax.php
 * This file is part of MOVIM.
 * 
 * @brief This is movim's ajax server.
 *
 * @author Etenil <etenil@etenilsrealm.nl>
 *
 * @version 1.0
 * @date  7 November 2010
 *
 * Copyright (C)2010 MOVIM team
 * 
 * See the file `COPYING' for licensing information.
 */
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
ini_set('log_errors', 1);
ini_set('display_errors', 0);
ini_set('error_reporting', E_ALL ^ E_DEPRECATED ^ E_NOTICE);
ini_set('error_log', ROOTDIR.'/log/php.log');

// Run
require_once('init.php');

set_time_limit(200);

$polling = true;

$rpc = new RPC();
$rpc->handle_json();

// Closing stuff
WidgetWrapper::destroyInstance();

?>
