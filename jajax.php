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

define('DOCUMENT_ROOT', dirname(__FILE__));
define('DOCTYPE','application/json');
require_once(DOCUMENT_ROOT.'/bootstrap.php');
try {
    $bootstrap = new Bootstrap();
    $booted = $bootstrap->boot();

    set_time_limit(200);

    $polling = true;

    $rpc = new RPC();
    $rpc->handle_json();

    // Closing stuff
    WidgetWrapper::destroyInstance();
} catch(\Exception $e) {
    if(isset($_GET['fail_safe']) && (int)$_GET['fail_safe']) {
        $rpc = new RPC();
        $rpc->handle_json();
    }
    
    print $e->getMessage();
}
?>
