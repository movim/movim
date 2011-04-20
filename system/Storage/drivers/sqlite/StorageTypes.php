<?php

/**
 * @file StorageTypes.php
 * This file is part of Movim.
 * 
 * @brief Defines types of SQLite.
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

/**
 * Basic storage class that is extended by all others.
 */
class StorageTypeSQLite extends StorageTypeBase
{
    protected $params;
    protected $def = "BASE";

    public function __construct()
    {
        $this->params = func_get_args();
    }
    
    /**
     * Generates the creation statement.
     */
    public function create_stmt()
    {
        $opt = "";
        if(count($this->params) > 0) {
            $opt = "(".implode(', ', $this->params).")";
        }

        return $this->def . $opt;
    }
}

class StorageTypeBigInt extends StorageTypeSQLite
{
    protected $def = "BIGINT";
}

class StorageTypeBool extends StorageTypeSQLite
{
    protected $def = "BOOLEAN";
}

class StorageTypeVarChar extends StorageTypeSQLite
{
    protected $def = "VARCHAR";
}

class StorageTypeDate extends StorageTypeSQLite
{
    protected $def = "DATE";
}

class StorageTypeDateTime extends StorageTypeSQLite
{
    protected $def = "DATETIME";
}

class StorageTypeDecimal extends StorageTypeSQLite
{
    protected $def = "DECIMAL";
}

class StorageTypeFloat extends StorageTypeSQLite
{
    protected $def = "FLOAT";
}

class StorageTypeInt extends StorageTypeSQLite
{
    protected $def = "INTEGER";
}

class StorageTypeText extends StorageTypeSQLite
{
    protected $def = "TEXT";
}

class StorageTypeBlob extends StorageTypeSQLite
{
    protected $def = "BLOB";
}

class StorageTypeForeignKey extends StorageTypeSQLite
{
    // TODO protected $def = "";
}

?>
