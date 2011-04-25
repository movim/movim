<?php

/**
 * @file StorageEngineBase.php
 * This file is part of PROJECT.
 *
 * @brief Basic implementation and utilities to create storage drivers.
 *
 * @author Etenil <etenil@etenilsrealm.nl>
 *
 * @version 1.0
 * @date 18 April 2011
 *
 * Copyright (C)2011 Movim
 *
 * All rights reserved.
 */

class StorageEngineBase implements StorageDriver
{
    /**
     * Creates the storage object (table) associated to the object.
     */
    public function create_storage($object)
    {
    }

    /**
     * Saves the object into its storage.
     */
    public function save($object)
    {
    }

    /**
     * Deletes the object from its storage.
     */
    public function delete($object)
    {
    }

    /**
     * Loads up the data corresponding to the object in the storage.
     */
    public function select($object, array $cond)
    {
    }

    /**
     * Deletes the storage associated to the object.
     */
<<<<<<< HEAD
    public function drop($object)
=======
    public function drop($outp)
>>>>>>> storage
    {
    }

    /**
     * Logs or prints the query depending on the status of the constant
     * DB_DEBUG.
     */
    protected function log($query)
    {
        if(defined('DB_DEBUG')) {
            $logstr = date("Y-m-d H:i:s :: ") . $query . "\n";
            if(strtolower(DB_DEBUG) == 'on') {
                echo $logstr;
            }
            else if(defined('DB_LOGFILE')) {
                $fh = fopen(DB_LOGFILE, 'a');
                fwrite($fh, $logstr);
                fclose($fh);
            }
        }
    }
    
    /**
     * Checks that object is a storable object.
     */
    protected function is_storage($object)
    {
        return StorageEngineBase::does_extend($object, "StorageBase");
    }

    /**
     * Checks that object is storable or throw an exception.
     */
    protected function require_storage($object)
    {
        if(!StorageEngineBase::does_extend($object, "StorageBase")) {
            throw new StorageException(t("Provided object is not storable."));
        }
    }

    /**
     * Determines if the given object extends StorageTypeBase.
     */
    public static function does_extend($object, $par_name)
    {
        if(!is_object($object)) {
            return false;
        }
        
        $refl = null;
        try {
            $refl = new ReflectionClass($object);
        }
        catch(ReflectionException $e) {
            return false;
        }

        while($refl = $refl->getParentClass()) {
            if($refl->getName() == $par_name) {
                return true;
            }
        }

        return false;
    }

    /**
     * Convenience helper that wraps a reflection class call.
     */
    protected function getObjName($object)
    {
        $refl = new ReflectionClass($object);
        return $refl->getName();
    }
}

?>
