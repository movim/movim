<?php

/**
 * @file MovimRPC.php
 * This file is part of PROJECT.
 * 
 * @brief Description
 *
 * @author Etenil <etenil@etenilsrealm.nl>
 *
 * @version 1.0
 * @date 20 February 2011
 *
 * Copyright (C)2011 Etenil
 * 
 * All rights reserved.
 */

class MovimRPC_Exec
{
    protected $funcalls;

    function __construct()
    {
        $this->funcalls = array();
    }

    function addCall($call)
    {
        if(is_object($call)) {
            $this->funcalls[] = $call;
        }
    }

    function addCalls(array $calls)
    {
        foreach($calls as $call) {
            $this->addCall($call);
        }
    }
    
    function exec()
    {

        // Cleaning rubbish.
        ob_clean();
        ob_start();

        // Starting XML output.
        header('Content-Type: text/xml');
        println('<?xml version="1.0" encoding="UTF-8" ?>');
        println('<movimcontainer>');

        foreach($this->funcalls as $funcall) {
            echo $funcall->genXML();
        }
        println('</movimcontainer>');

        $xml = ob_get_flush();
        file_put_contents('debug', $xml . "\n" . var_export($this->funcalls, true));
    }
}

?>