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
    public function __construct($db_file)
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
            while($table[] = $res->fetchArray(SQLITE3_ASSOC)) {}
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

    public function create_storage($object)
    {
        $this->require_storage($object);

        $props = $object->walkprops("getme");

        $stmt = 'CREATE TABLE IF NOT EXISTS "'.$this->getObjName($object).'" ('.
            '"id" INTEGER NOT NULL PRIMARY KEY AUTOINCREMENT, ';
        foreach($props as $prop) {
            $stmt .= $this->create_stmt($prop) . ', ';
        }

        // Stripping the extra ', ' and closing the statement.
        $stmt = substr($stmt, 0, -2) . ');';

        return $this->query($stmt);
    }

    public function save($object)
    {
        $this->require_storage($object);

        $stmt = "";

        $props = $object->walkprops("getval");

        if(!$object->id) {
            $stmt = "INSERT INTO " . $this->getObjName($object);

            $cols = "";
            $vals = "";
            foreach($props as $prop) {
                $cols.= $prop['name'] . ', ';
                if(StorageEngineBase::does_extend($prop['val'], "StorageBase")) {
                    $vals.= '"' . $prop['val']->id . '", ';
                } else {
                    $vals.= '"' . $prop['val'] . '", ';
                }
            }

            $stmt.= '(' . substr($cols, 0, -2) . ')';
            $stmt.= ' VALUES(' . substr($vals, 0, -2) . ');';

            $this->query($stmt);
            return $this->lastId();
        } else {
            $stmt = "UPDATE " . $this->getObjName($object) . " SET ";

            foreach($props as $prop) {
                $stmt.= $prop['name'] . '="' . $prop['val'] . '", ';
            }

            $stmt = substr($stmt, 0, -2) . ' WHERE id="' . $object->id . '";';

            return $this->query($stmt);
        }
    }

    public function delete($object)
    {
        $this->require_storage($object);

        // Does the object exist in the storage?
        if($object->id) {
            $stmt = "DELETE FROM " . $this->getObjName($object) . " WHERE id=\"" . $object->id . "\";";

            return $this->query($stmt);
        }
    }

    public function drop($object)
    {
        $this->require_storage($object);

        $stmt = 'DROP TABLE IF EXISTS '.$this->getObjName($object).';';

        return $this->query($stmt);
    }

    /**
     * Returns data relative to an object as an array.
     */
    public function select($object, array $cond)
    {
        $stmt = "SELECT * FROM " . $this->getObjName($object);

        if(count($cond) > 1) {
            $where . " WHERE ";

            foreach($cond as $col => $val) {
                $stmt.= "$col=\"$val\" AND ";
            }

            // Stripping the extra " AND "
            $stmt = substr($stmt, 0, -5) . ';';
        }

        $this->log($stmt);

        return $this->query($stmt);
    }
}

?>
