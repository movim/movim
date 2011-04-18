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
        if($refl->getParentClass() != "StorageBase") {
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
            $prefl = new ReflectionClass($prop->getValue($object));
            // Must be a storable property.
            if($prefl->getParentClass() != "StorageTypeBase") {
                continue;
            }
            $stmt[] = array(
                'name' => $prop->getName(),
                'val' => call_user_func_array(
                    array($prop->getValue($object), "create_stmt"),
                    $args));
        }
    }

    /**
     * Convenience helper that wraps a reflection class call.
     */
    protected function getObjName($object)
    {
        $refl = new ReflectionClass($object);
        return $object->getName();
    }
}

?>
