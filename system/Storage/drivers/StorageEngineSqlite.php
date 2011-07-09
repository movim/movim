<?php

/**
 * @file StorageEngine.php
 * This file is part of Movim.
 *
 * @brief Implements a storage driver for sqlite.
 *
 * @author Etenil <etenil@etenilsrealm.nl>
 *
 * @version 1.0
 * @date 17 April 2011
 *
 * Copyright (C)2011 Movim
 *
 * All rights reserved.
 */

class StorageEngineSqlite extends StorageEngineBase implements StorageDriver
{
    protected $db;

    // Loading config and attempting to connect.
    public function __construct()
    {
        $args = func_get_args();
        if(count($args) > 0) {
            call_user_func_array(array($this, 'init'), $args);
        }
    }

    public function init($db_file)
    {
        // Checking the file can be accessed.
        if($db_file == "") {
            throw new StorageException(t("The database file must be specified."));
        }

        // OK, trying to open the file.
        $this->db = new SQLite3($db_file);
    }

    public function __destruct()
    {
        if($this->db) {
            $this->db->close();
        }
    }

    /**
     * Checks SQLite errors. Throws a StorageException if there was an error
     * during the last query.
     */
    protected function errors()
    {
        $error = $this->db->lastErrorCode();
        if($error != 0) {
            throw new StorageException(
                t(" `%s'", $db_file),
                $this->db->lastErrorMsg(),
                $error);
        }
    }

    /**
     * SQLite-specific routine with error check included.
     */
    protected function query($statement)
    {
        $ret = null;

        $this->log($statement);

        if(strtoupper(substr(trim($statement), 0, 6)) == "SELECT") {
            $res = $this->db->query($statement);
            $this->errors();

            $table = array();
            while($row = $res->fetchArray(SQLITE3_ASSOC))
            {
                $table[] = $row;
            }
            return $table;
        } else {
            $answer = $this->db->exec($statement);
            $this->errors();
            return $answer;
        }
    }

    protected function lastId()
    {
        return $this->db->lastInsertRowId();
    }

    /**
     * Generates the creation statement.
     */
    protected function create_stmt(&$type)
    {
        $typename = $type['val']->gettype();
        $def = $type['name'] . ' ';
        switch($typename) {
        case 'StorageTypeBigInt':
            $def.= 'BIGINT';
            break;
        case 'StorageTypeBool':
            $def.= 'BOOLEAN';
            break;
        case 'StorageTypeVarChar':
            $def.= 'VARCHAR('.$type['val']->length.')';
            break;
        case 'StorageTypeDate':
            $def.= 'DATE';
            break;
        case 'StorageTypeDateTime':
            $def.= 'DATETIME';
            break;
        case 'StorageTypeDecimal':
            $def.= 'DECIMAL('.$type['val']->length.', '.$type['val']->decimal_places.')';
            break;
        case 'StorageTypeFloat':
            $def.= 'FLOAT';
            break;
        case 'StorageTypeInt':
            $def.= 'INTEGER';
            break;
        case 'StorageTypeText':
            $def.= 'TEXT';
            break;
        case 'StorageTypeBlob':
            $def.= 'BLOB';
            break;
        case 'StorageTypeForeignKey':
            $def.= 'INTEGER NOT NULL REFERENCES '.$type['val']->model.'('.$type['val']->field.')';
            break;
        }

        return $def;
    }

    public function create(&$object)
    {
        $this->require_storage($object);

        $proto = $object->prototype();

        $stmt = 'CREATE TABLE IF NOT EXISTS "'.$this->obj_name($object).'" ('.
            '"id" INTEGER NOT NULL PRIMARY KEY AUTOINCREMENT, ';
        foreach($proto as $prop) {
            $stmt .= $this->create_stmt($prop) . ', ';
        }

        // Stripping the extra ', ' and closing the statement.
        $stmt = substr($stmt, 0, -2) . ');';

        return $this->query($stmt);
    }

    public function save(&$object)
    {
        $this->require_storage($object);

        $stmt = "";

        $props = $object->prototype();

        if(!$object->id) {
            $stmt = "INSERT INTO " . $this->obj_name($object);

            $cols = "";
            $vals = "";
            foreach($props as $prop) {
                $cols.= $prop['name'] . ', ';
                if(StorageEngineBase::does_extend($prop['val'], "StorageBase")) {
                    $vals.= '"' . $prop['val']->id . '", ';
                } else {
                    $vals.= '"' . $prop['val']->getval() . '", ';
                }
            }

            $stmt.= '(' . substr($cols, 0, -2) . ')';
            $stmt.= ' VALUES(' . substr($vals, 0, -2) . ');';

            $this->query($stmt);
            $object->setid($this->lastId());
        } else {
            $stmt = "UPDATE " . $this->obj_name($object) . " SET ";

            $cols = "";
            $vals = "";
            foreach($props as $prop) {
                $stmt.= $prop['name'] . '=';
                if(StorageEngineBase::does_extend($prop['val'], "StorageBase")) {
                    $stmt.= '"' . $prop['val']->id . '", ';
                } else {
                    $stmt.= '"' . $prop['val']->getval() . '", ';
                }
            }

            $stmt = substr($stmt, 0, -2) . ' WHERE id="' . $object->id . '"';

            return $this->query($stmt);
        }
    }

    public function delete(&$object)
    {
        $this->require_storage($object);

        // Does the object exist in the storage?
        if($object->id) {
            $stmt = "DELETE FROM " . $this->obj_name($object) . " WHERE id=\"" . $object->id . "\";";

            $result = $this->query($stmt);

            if($result) {
                $object->clearid();
            }

            return $result;
        }
    }

    public function drop(&$object)
    {
        $this->require_storage($object);

        $stmt = 'DROP TABLE IF EXISTS '.$this->obj_name($object).';';

        $result = $this->query($stmt);

        if($result) {
            $object->clearid();
        }

        return $result;
    }

    /**
     * Returns data relative to an object as an array.
     */
    public function load(&$object, array $cond)
    {
        $stmt = "SELECT * FROM " . $this->obj_name($object);

        if(count($cond) > 0) {
            $stmt.= " WHERE ";

            foreach($cond as $col => $val) {
                $stmt.= "$col=\"$val\" AND ";
            }

            // Stripping the extra " AND "
            $stmt = substr($stmt, 0, -5) . ';';
        }

        $this->log($stmt);

        $data = $this->query($stmt);

        if(count($data) < 1) {
            return false;
        }

        $data = $data[0];

        // Populating the object.
        $props = $object->prototype();

        foreach($props as $prop) {
            if(isset($data[$prop['name']])) {
                $object->__set($prop['name'], $data[$prop['name']]);
            }
        }

        return true;
    }

    /**
     * Loads a bunch of objects of a given type.
     */
    public function select($objecttype, array $cond)
    {
        $stmt = "SELECT * FROM " . $objecttype;

        if(count($cond) > 0) {
            $stmt.= " WHERE ";

            foreach($cond as $col => $val) {
                $stmt.= "$col=\"$val\" AND ";
            }

            // Stripping the extra " AND "
            $stmt = substr($stmt, 0, -5) . ';';
        }

        $this->log($stmt);

        $data = $this->query($stmt);

        if(count($data) < 1) {
            return false;
        }

        $objs = array();

        foreach($data as $row) {
            $object = new $objecttype();
            // Populating the object.
            $props = $object->prototype();

            foreach($props as $prop) {
                if(isset($row[$prop['name']])) {
                    $object->__set($prop['name'], $row[$prop['name']]);
                }
            }

            $objs[] = $object;
        }

        return $objs;
    }
}

?>
