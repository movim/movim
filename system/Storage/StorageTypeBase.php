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
    protected $val;

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
        }
    }

    public function getval()
    {
        return $this->val;
    }
}

?>