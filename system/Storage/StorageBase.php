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

class StorageBase
{
    protected $db;
    protected $id = false;
    public $children;

    /**
     * Constructor.
     */
    public function __construct($id = 0)
    {
        // Loading driver.
        $conf = new Conf();
        $driver = $conf->getServerConfElement("storageDriver");
        require_once("drivers/${driver}/init.php");
        $this->db = new StorageEngine();

        $this->type_init();

        $this->children = new StorageCollection($this);
        
        if($id > 0) {
            $this->load(array('id' => $id));
        }
    }

    public function __get($name)
    {
        if(isset($this->$name) && $this->is_typed($this->$name)) {
            return $this->$name->getval();
        }
        else if($name == 'id') {
            return $this->id;
        }
        else {
            throw new StorageException(t("Attempting to access private member `%s' of class `%s'.", $name, get_class($this)));
        }
    }

    public function __set($name, $value)
    {
        if(isset($this->$name) && $this->is_typed($this->$name)) {
            return $this->$name->setval($value);
        } else {
            debug_print_backtrace();
            throw new StorageException(t("Attempting to access private member `%s' of class `%s'.", $name, get_class($this)));
        }
    }

    /**
     * Initialize types in here.
     */
    protected function type_init()
    {
    }

    /**
     * Creates the storage associated with the object e.g. a SQL table.
     */
    public function create()
    {
        return $this->db->create_storage($this);
    }

    /**
     * Saves the class into the chosen container driver.
     */
    public function save()
    {
        $ret = $this->db->save($this);

        $ret .= $this->children->save();
        
        if(!$this->id) {
            $this->id = $ret;
        }

        return $ret;
    }

    /**
     * Deletes this object from the storage.
     */
    public function delete()
    {
        $ret = $this->db->delete($this);

        // Deleting in cascade.
        $ret .= $this->children->delete();
        
        // Resetting id.
        $this->id = false;

        return $ret;
    }

    /**
     * Deletes associated storage.
     */
    public function drop()
    {
        $ret = $this->db->drop($this);

        $ret .= $this->children->drop();

        $this->id = false;
        
        return $ret;
    }

    /**
     * Determines if the given object extends StorageTypeBase.
     */
    protected function is_typed($object)
    {
        return StorageEngineBase::does_extend($object, "StorageTypeBase");
    }

    /**
     * Loads up the object.
     */
    public function load(array $cond)
    {
        $data = $this->db->select($this, $cond);
 
 
        // OK now let's populate our properties.
        if(is_array($data[0]) && count($data[0]) > 0) {
            foreach($data[0] as $name => $value) {
                if($name != "id" && !isset($this->$name)) {
                    continue;
                }
                else if($name == "id") {
                    $this->id = $value;
                }
                else {
                    $this->$name->setval($value);
                }
            }
        }

        // Loading the children.
        $this->children->load($this->id);

        return true;
    }

    /**
     * Fetches all objects of this type.
     */
    public static function objects(array $cond = array())
    {
        $classname = __CLASS__;
        $data = null;
        if(is_object($this)) {
            $data = $this->db->select($this, $cond);
        } else {
            $data = $this->db->select(new $classname(), $cond);
        }

        // We instanciate an object per row.
        $objects = array();
        foreach($data as $obj_dat) {
            $obj = new $classname();
            foreach($data as $name => $value) {
                if($name != "id" && !isset($this->$name)) {
                    continue;
                }
                else if($name == "id") {
                    $obj->id = $value;
                }
                else {
                    $obj->$name->setval($value);
                }
            }
            $objects[$obj->id] = $obj;
        }

        return $objects;
    }

    /**
     * Handy function, in particular for debugging. Overloading it is a good idea.
     */
    public function tostring()
    {
        $buffer = "(".get_class($this)." id: " . (($this->id != false)? $this->id : 'New') . ") {\n";
        foreach($this as $propname => $propval) {
            if($this->is_typed($propval)) {
                $buffer.=
                    "    [" . $propname . ": '" . $propval->getval() . "'] \n";
            }
        }

        return $buffer . "}\n";
    }

    /**
     * Helper to assign a foreign key to a member variable.
     */
    protected function foreignkey($var, $class)
    {
        $this->$var = StorageType::foreignkey(get_class($this), $var, $class);
    }

    /**
     * executes the given action on all props derived from StorageTypeBase.
     *
     * Extra parameters are passed on to the called method.
     *
     *   walkprops($action, ...)
     */
    public function walkprops($action)
    {
        $stmt = array();

        // Are there extra args?
        $args = array();
        if(count(func_get_args()) > 2) {
            $args = array_slice(func_get_args(), 2);
        }

        foreach($this as $propname => $propval) {
            // Must be a storable property.
            if($this->is_typed($propval)) {
                $stmt[] = array(
                    'name' => $propname,
                    'val' => call_user_func_array(
                        array($propval, $action),
                        $args));
            }
        }

        return $stmt;
    }
}

?>
