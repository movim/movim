<?php
/**
 * @file StorableCollection.php
 * This file is part of MOVIM.
 *
 * @brief A object that contains StorageBase-derived objects. It is mostly used
 * to contain children of StorageBase objects within themselves.
 *
 * This is also a class factory that can instanciate new objects bound to the
 * original.
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

class StorageCollection
{
    protected $objects = array();
    protected $mother;

    public function __construct(&$mother)
    {
        $this->mother = $mother;
    }

    public function load($id)
    {
        $children = StorageSchema::get_relations(get_class($this->mother));

        foreach($children as $relation) {
            $this->objects[$rel['class']] =
                $child_class::objects(array($rel['var'] => $id));
        }
    }

    public function add(&$obj)
    {
        $children = StorageSchema::get_child_classes(get_class($this->mother));

        if(in_array(get_class($obj), $children)) {
            $this->objects[get_class($obj)][] = &$obj;
        }

        /*$children = StorageSchema::get_relations(get_class($this->mother));

        $var = "";
        foreach($children as $rel) {
            if($rel['class'] == get_class($obj)) {
                $var = $rel['var'];
                break;
            }
        }

        if($var != "") {
            $obj->$var = $this->mother->id;
            }*/

        return $obj;
    }

    protected function walk_objects($func, array $args)
    {
        $outp = array();
        foreach($this->objects as $objects) {
            foreach($objects as $object) {
                $outp[] = call_user_func_array(array($object, $func), $args);
            }
        }

        return $outp;
    }

    public function save()
    {
        return $this->walk_objects('save', func_get_args());
    }

    public function delete()
    {
        return $this->walk_objects('delete', func_get_args());
    }

    public function drop()
    {
        return $this->walk_objects('drop', func_get_args());
    }
}

?>