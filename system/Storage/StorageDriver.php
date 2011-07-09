<?php

/**
 * @file StorageDriver.php
 * This file is part of Movim.
 *
 * @brief The necessary template of a storage driver for Movim.
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

interface StorageDriver
{
    public function create(&$object);
    public function save(&$object);
    public function delete(&$object);
    public function load(&$object, array $cond);
    public function select($objecttype, array $cond);
    public function drop(&$object);
}

?>
