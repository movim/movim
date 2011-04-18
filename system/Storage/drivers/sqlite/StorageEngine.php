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
        $conf = new Conf();
        $db_file = $conf->getServerConfElement("SqliteFile");

        // Checking the file can be accessed.
        if($db_file == "") {
            throw new StorageException(t("The database file must be specified."));
        }

        // OK, trying to open the file.
        $this->db = sqlite_open($db_file);

    }

    /**
     * Checks SQLite errors. Throws a StorageException if there was an error
     * during the last query.
     */
    protected function errors()
    {
        $error = sqlite_last_error($this->db);
        if($error != 0) {
            throw new StorageException(
                t("Couldn't open file `%s'", $db_file),
                sqlite_error_string($error),
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
            $ret = sqlite_array_query($this->db, $statement);
        } else {
            $ret = sqlite_query($this->db, $statement);
        }

        $this->errors();

        return $ret;
    }
    
    public function create_storage($object, $outp = false)
    {
        $props = $this->walkprops($object, "create_stmt");

        $stmt = 'CREATE TABLE "'.$this->getObjName($object).'" ('.
            '"id" serial NOT NULL PRIMARY KEY, ';
        foreach($props as $prop) {
            $stmt = '"' . $prop['name'] . '" ' . $prop['val'] . ', ';
        }

        // Stripping the extra ', ' and closing the statement.
        $stmt = substr($stmt, 0, strlen($stmt - 2)) . ');';

        if($outp) {
            return $stmt;
        } else {
            $this->query($stmt);
        }
    }

    public function save($object, $outp = false)
    {
    }
    
    public function delete($object, $outp = false)
    {
    }
}

?>
