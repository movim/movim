<?php

/**
 * @file StorableBase.php
 * This file is part of MOVIM.
 *
 * @brief Basic implementation of a self-generating storable object. This class
 * is not made to be used straight-away but to be extended.
 *
 * @author Guillaume Pasquet <etenil@etenilsrealm.nl>
 *
 * @version 1.0
 * @date 13 April 2011
 *
 * Copyright (C)2011 MOVIM Project
 *
 * All rights reserved, see included COPYING file for licensing information.
 */

require_once("Driver/StorageDriver.php");

class StorageBase
{
    protected $db;
    
    /**
     * Constructor.
     */
    public function __construct()
    {
        // Loading driver.
        $conf = new Conf();
        $driver = $conf->getServerConfElement("storageDriver");
        require_once("drivers/${driver}/init.php");
        $this->db = new StorageEngine();
    }

    /**
     * Creates the storage associated with the object e.g. a SQL table.
     * @param simulate will make the method return the queries instead of
     * running them. Useful for testing.
     */
    public function create($simulate = false)
    {
        foreach($this as $propname => $propval) {
            $refl = new ReflectionClass($propval);

            // Is this property a storage?
            if($refl->getParentClass() == "StorageTypeBase") {
                
            }
		}
    }

    /**
     * Saves the class into the chosen container driver.
     */
    public function save($simulate = false)
    {
    }

    /**
     * Deletes this object from the storage.
     */
    public function delete($simulate = false)
    {
    }
}

?>
