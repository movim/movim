<?php

/**
 * @file StorageType.php
 * This file is part of Movim.
 *
 * @brief Convenience class to spawn types.
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

class StorageType
{
    private function __construct() {}

    public static function bigint()
    {
        return new StorageTypeBigInt();
    }

    public static function bool()
    {
        return new StorageTypeBool();
    }

    public static function varchar($length)
    {
        return new StorageTypeVarChar($length);
    }

    public static function date()
    {
        return new StorageTypeDate();
    }

    public static function datetime()
    {
        return new StorageTypeDateTime();
    }

    public static function decimal($length, $decimal_places)
    {
        return new StorageTypeDecimal($length, $decimal_places);
    }

    public static function float()
    {
        return new StorageTypeFloat();
    }

    public static function int()
    {
        return new StorageTypeInt();
    }

    public static function text()
    {
        return new StorageTypeText();
    }

    public static function blob()
    {
        return new StorageTypeBlob();
    }

    public static function foreignkey($mother, $var, $child)
    {
        // Attaching to model.
        StorageSchema::register_child_class($mother, $child, $var);
        return new StorageTypeForeignKey($mother, $var);
    }
}

?>
