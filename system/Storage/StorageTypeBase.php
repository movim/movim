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
 * Handy exception.
 */
class StorageTypeException extends Exception
{
    public function __construct($message) { parent::__construct($message); }
}

/**
 * Basic storage class that is extended by all others.
 */
class StorageTypeBase
{
    protected $val;

    public function __construct()
    {
        $this->params = func_get_args();
    }
    
    /**
     * Extend to check input types.
     */
    protected function check_input($value)
    {
        return true;
    }

    public function setval($value)
    {
        if($this->check_input($value)) {
            $this->val = $value;
            return $value;
        } else {
            throw new StorageTypeException(t("Provided value `%s' is not of correct type.", $value));
        }
    }

    public function getval()
    {
        return $this->val;
    }

    public function gettype()
    {
        return get_class($this);
    }

    public function getme()
    {
        return $this;
    }
}

class StorageTypeBigInt extends StorageTypeBase
{
    protected function check_input($value)
    {
        return is_numeric($value);
    }
}

class StorageTypeBool extends StorageTypeBase
{
}

class StorageTypeVarChar extends StorageTypeBase
{
    public $length;
    
    function __construct($length)
    {
        $this->length = $length;
    }
}

class StorageTypeDate extends StorageTypeBase
{
}

class StorageTypeDateTime extends StorageTypeBase
{
}

class StorageTypeDecimal extends StorageTypeBase
{
    public $length;
    public $decimal_places;
    
    function __construct($length, $decimal_places)
    {
        $this->length = $length;
        $this->decimal_places = $decimal_places;
    }
}

class StorageTypeFloat extends StorageTypeBase
{
}

class StorageTypeInt extends StorageTypeBase
{
}

class StorageTypeText extends StorageTypeBase
{
}

class StorageTypeBlob extends StorageTypeBase
{
}

class StorageTypeForeignKey extends StorageTypeBase
{
    public $model;
    public $field;

    public function __construct($model, $field = 'id')
    {
        $this->model = $model;
        $this->field = $field;
    }
}

?>