<?php

/**
 * @file StorableBase.php
 * This file is part of MOVIM.
 *
 * @brief Basic implementation of a self-generating storable object. This class
 * is not made to be used straight-away but to be extended.
 *
 * @author Guillaume Pasquet <etenil@etenilsrealm.nl>
 *
 * @version 1.0
 * @date 13 April 2011
 *
 * Copyright (C)2011 MOVIM Project
 *
 * All rights reserved, see included COPYING file for licensing information.
 */

class StorableBase
{
    /**
     * Constructor.
     */
    public function __construct()
    {
    }

    /**
     * Creates the storage associated with the object e.g. a SQL table.
     */
    public function create()
    {
    }

    /**
     * Saves the class into the chosen container driver.
     */
    public function save()
    {
    }

    /**
     * Deletes this object from the storage.
     */
    public function delete()
    {
    }
}

?>
