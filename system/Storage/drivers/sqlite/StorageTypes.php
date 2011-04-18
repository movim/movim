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
class StorageTypeBase
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

        return $def . $opt;
    }
}

class StorageTypeBigInt extends StorageTypeBase
{
    protected $def = "BIGINT";
}

class StorageTypeBool extends StorageTypeBase
{
    protected $def = "BOOLEAN";
}

class StorageTypeVarChar extends StorageTypeBase
{
    protected $def = "VARCHAR";
}

class StorageTypeDate extends StorageTypeBase
{
    protected $def = "DATE";
}

class StorageTypeDateTime extends StorageTypeBase
{
    protected $def = "DATETIME";
}

class StorageTypeDecimal extends StorageTypeBase
{
    protected $def = "DECIMAL";
}

class StorageTypeFloat extends StorageTypeBase
{
    protected $def = "FLOAT";
}

class StorageTypeInt extends StorageTypeBase
{
    protected $def = "INTEGER";
}

class StorageTypeText extends StorageTypeBase
{
    protected $def = "TEXT";
}

class StorageTypeBlob extends StorageTypeBase
{
    protected $def = "BLOB";
}

class StorageTypeForeignKey extends StorageTypeBase
{
    // TODO protected $def = "";
}

?>
