<?php
/**
 * @file loader.php
 *
 * This file is part of MOVIM.
 *
 * @brief Loads up classes and stuff for Storage.
 *
 * @author Guillaume Pasquet <etenil@etenilsrealm.nl>
 *
 * @version 1.0
 * @date  1 June 2011
 *
 * Copyright (C)2011 MOVIM Project
 *
 * All rights reserved, see COPYING for licensing information.
 */

// Function to easily load a Storage Engine.
function storage_load_driver($drivername)
{
    require($base."drivers/StorageEngine".ucfirst(strtolower($drivername)).".php");
}

function load_storage(array $drivers)
{
    $base = dirname(__FILE__).'/';

    require($base.'StorageBase.php');
    require($base.'StorageCollection.php');
    require($base.'StorageDriver.php');
    require($base.'StorageEngineBase.php');
    require($base.'StorageEngineWrapper.php');
    require($base.'StorageException.php');
    require($base.'StorageSchema.php');
    require($base.'StorageTypeBase.php');
    require($base.'StorageType.php');

    // Now loading the drivers.
    foreach($drivers as $driver) {
        $driver_init = $base.'drivers/'.$driver.'/init.php';
        if(file_exists($driver_init)) {
            require($driver_init);
        }
    }
}

?>