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

class StorageEngine extends StorageEngineBase implements StorageDriver
{
    protected $db;
    
    // Loading config and attempting to connect.
    public function __construct()
    {
        //$conf = new Conf();
        //$db_file = $conf->getServerConfElement("SqliteFile");
        $db_file = BASE_PATH . "test.sqlite";

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
        
        if(strtoupper(substr(trim($statement), 0, 6)) == "SELECT") {
            $res = $this->db->query($statement);

            $table = array();
            while($table[] = $res->fetchArray(SQLITE3_ASSOC)) {}
            return $table;
        } else {
            return $this->db->exec($statement);
        }
    }
    
    public function create_storage($object, $outp = false)
    {
        $props = $this->walkprops($object, "create_stmt");

        $stmt = 'CREATE TABLE "'.$this->getObjName($object).'" ('.
            '"id" serial NOT NULL PRIMARY KEY, ';
        foreach($props as $prop) {
            $stmt .= '"' . $prop['name'] . '" ' . $prop['val'] . ', ';
        }

        // Stripping the extra ', ' and closing the statement.
        $stmt = substr($stmt, 0, -2) . ');';

        if($outp) {
            return $stmt;
        } else {
            return $this->query($stmt);
        }
    }

    public function save($object, $outp = false)
    {
        $stmt = "";
        
        $props = $this->walkprops($object, "getval");
        
        var_dump($props);

        if(!isset($object->id) || !is_numeric($object->id)) {
            $stmt = "INSERT INTO " . $this->getObjName($object);

            $cols = "";
            $vals = "";
            foreach($props as $prop) {
                $cols.= $prop['name'] . ', ';
                $vals.= '"' . $prop['val'] . '", ';
            }

            $stmt.= '(' . substr($cols, 0, -2) . ')';
            $stmt.= ' VALUES(' . substr($vals, 0, -2) . ');';
        } else {
            $stmt = "UPDATE " . $this->getObjName($object) . " SET ";

            foreach($props as $prop) {
                $stmt.= $prop['name'] . '="' . $prop['val'] . '", ';
            }

            $stmt = substr($stmt, 0, -2) . ' WHERE id="' . $object->id . '";';
        }

        if($outp) {
            return $stmt;
        } else {
            return $this->query($stmt);
        }
    }
    
    public function delete($object, $outp = false)
    {
    }

    /**
     * Returns data relative to an object as an array.
     */
    public function select($object, array $cond, $outp = false)
    {
        if(count($cond) < 1) {
            return false;
        }

        $stmt = "SELECT * FROM " . $this->getObjName($object) . " WHERE ";
        
        foreach($cond as $col => $val) {
            $stmt.= "$col=\"$val\" AND ";
        }

        // Stripping the extra " AND "
        $stmt = substr($stmt, 0, -5) . ';';

        if($outp) {
            return $stmt;
        } else {
            return $this->query($stmt);
        }
    }
}

?>
