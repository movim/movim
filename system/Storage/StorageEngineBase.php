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
    public function create_storage($object, $outp = false)
    {
    }

    public function save($object, $outp = false)
    {
    }

    public function delete($object, $outp = false)
    {
    }

    public function select($object, array $cond, $outp = false)
    {
    }

    /**
     * executes the given action on all props derived from StorageTypeBase in
     * $object -- if object itself extends StorageBase.
     *
     * Extra parameters are passed on to the called method.
     *
     *   walkprops($object, $action, ...)
     */
    protected function walkprops($object, $action)
    {
        $refl = new ReflectionClass($object);
        $parent = $refl->getParentClass();
        if($parent->getName() != "StorageBase") {
            throw new StorageException(t("Provided object is not storable."));
        }

        $props = $refl->getProperties();
        $stmt = array();

        // Are there extra args?
        $args = array();
        if(count(func_get_args()) > 2) {
            $args = array_slice(func_get_args(), 2);
        }

        foreach($props as $prop) {
            if($prop->isPublic()) {
                // Must be a storable property.
                if($this->does_extend($prop->getValue($object), "StorageTypeBase")) {
                    $stmt[] = array(
                        'name' => $prop->getName(),
                        'val' => call_user_func_array(
                            array($prop->getValue($object), $action),
                            $args));
                }
            }
        }

        return $stmt;
    }

    /**
     * Determines if the given object extends StorageTypeBase.
     */
    public static function does_extend($object, $par_name)
    {
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
