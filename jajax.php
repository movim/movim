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

ini_set('log_errors', 1);
ini_set('display_errors', 0);
ini_set('error_reporting', E_ALL ^ E_DEPRECATED ^ E_NOTICE);
ini_set('error_log', 'log/php.log');

require("init.php");

$polling = true;

$rpc = new RPC();
$rpc->handle();
    
$widgets = WidgetWrapper::getInstance(false);
$widgets->iterateCached('saveCache');

// Closing stuff
WidgetWrapper::destroyInstance();
global $sdb;
$sdb->close();
?>
