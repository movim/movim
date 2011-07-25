<?php

/**
 * @file StorableSchema.php
 * This file is part of MOVIM.
 *
 * @brief Schema-aware class. Its job is to register and maintain relations
 * between StorageBase-derived objects, an apply actions recursively to
 * update/delete in cascade and so on.
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

class StorageSchema
{
    /**< Array that contains the relations. */
    private static $relations = array();
    private static $hierarchy = array();

    /** Constructor - not instanciable. */
    private function __construct()
    {
    }

    /**
     * Adds a relation between classes (and yes, classes are female).
     */
    public static function register_child_class($mother, $daughter, $onvar)
    {
        // Just checking...
        if(!is_array(self::$relations)) {
            self::$relations = array();
        }

        if(!isset(self::$relations[$mother])) {
            self::$relations[$mother] = array(array('class' => $daughter, 'var' => $onvar));
            self::$hierarchy[$mother] = array($daughter);
        } else {
            self::$relations[$mother][] = array('class' => $daughter, 'var' => $onvar);
            self::$hierarchy[$mother][] = $daughter;
        }
    }

    /**
     * Returns the list of classes children of the provided one.
     */
    public static function get_child_classes($mother)
    {
        // Just checking...
        if(!is_array(self::$hierarchy)) {
            self::$hierarchy = array();
        }

        if(isset(self::$hierarchy[$mother])) {
            return self::$hierarchy[$mother];
        } else {
            return array();
        }
    }

    /**
     * Returns the list of relations.
     */
    public static function get_relations($mother)
    {
        // Just checking...
        if(!is_array(self::$relations)) {
            self::$relations = array();
        }

        if(isset(self::$relations[$mother])) {
            return self::$relations[$mother];
        } else {
            return array();
        }
    }

    /**
     * Cascade creates children.
     */
    function cascade_create(&$storage, $mother)
    {
        $children = self::get_child_classes($mother);
        foreach($children as $child) {
            $daughter = new $child();
            $daughter->create($storage);
        }
    }

    /**
     * Cascade drop children.
     */
    function cascade_drop(&$storage, $mother)
    {
       $children = self::get_child_classes($mother);
        foreach($children as $child) {
            $daughter = new $child();
            $daughter->drop($storage);
        }
    }
}

?>