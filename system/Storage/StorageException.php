<?php

/**
 * @file StorageException.php
 * This file is part of Movim.
 * 
 * @brief Exception specialised in Databases.
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

class StorageException extends Exception
{
    function __construct($message, $detail = "", $code = -999)
    {
        $error = t("Storage error: %s", $message);

        if($code != -999) {
            $error.= "\n". t("Code: %d", $code);
        }
        
        if($detail != "") {
            $error.= "\n" . $detail;
        }
        
        parent::__construct($error);
    }

    function __toString() {
        return $this->message;
    }
}

?>
