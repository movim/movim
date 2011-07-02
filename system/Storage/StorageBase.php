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
    protected $id = false;
    public $children;

    /**
     * Constructor.
     */
    public function __construct(array $init = null)
    {
        $this->type_init();

        $this->children = new StorageCollection($this);

        if(is_array($init)) {
            $this->populate($init);
        }
    }

    /**
     * Sets the object's value based on the given array. The array's keys are
     * used as member variables's names and the associated values are set to
     * these.
     *
     * This only works with Storable member variables. Attempting to set any
     * other variable will result in a StorageException.
     */
    public function populate(array $vals)
    {
        foreach($vals as $varname => $varval) {
            if(isset($this->$varname) && $this->is_typed($this->$varname)) {
                $this->$varname->setval($varval);
            }
            else if(!isset($this->$varname)) {
                throw new StorageException(t("Unknown property %s", $varname));
            }
            else {
                throw new StorageException(t("Attempting to access private member `%s' of class `%s'.", $varname, get_class($this)));
            }
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
     * Determines if the given object extends StorageTypeBase.
     */
    protected function is_typed($object)
    {
        return StorageEngineBase::does_extend($object, "StorageTypeBase");
    }

    /**
     * Is this a linked object?
     */
    protected function is_child($object)
    {
        return StorageEngineBase::does_extend($object, "StorageTypeForeignKey");
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

    public function cascade($action)
    {
        $stmt = array();

        // Are there extra args?
        $args = array();
        if(count(func_get_args()) > 2) {
            $args = array_slice(func_get_args(), 2);
        }

        foreach($this as $propname => $propval) {
            // Must be a storable property.
            if($this->is_child($propval)) {
                $stmt[] = array(
                    'name' => $propname,
                    'val' => $propval->apply($action, $args),
                    );
            }
        }

        return $stmt;
    }

    /**
     * Returns the object's prototype.
     */
    public function prototype()
    {
        $proto = array();

        // Are there extra args?
        $args = array();
        if(count(func_get_args()) > 2) {
            $args = array_slice(func_get_args(), 2);
        }

        foreach($this as $propname => $prop) {
            // Must be a storable property.
            if($this->is_typed($prop)) {
                $proto[] = array(
                    'name' => $propname,
                    'val'  => $prop,
                    );
            }
        }

        return $proto;
    }

    /**
     * Sets the object's ID.
     */
    function setid($id)
    {
        if($this->id !== false) {
            throw new StorageException(t("Attempting to set the id of an existing object."));
        } else {
            $this->id = $id;
            return $this->id;
        }
    }

    /**
     * Unsets the object's ID.
     */
    function clearid()
    {
        $this->id = false;
    }
}

?>
