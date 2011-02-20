<?php

/**
 * @file MovimRPCFuncall.php
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

class MovimRPC_Call
{
    protected $func;
    protected $params;

    function __construct()
    {
        $this->params = array();
    }

    function setFunc($name)
    {
        $this->func = $name;
    }

    function addParam($parval)
    {
        $this->params[] = $parval;
    }

    function genXML()
    {
        $funcall = sprintln('<funcall name="%s">', $method);
        foreach($this->params as $param) {
            $funcall.= sprintln('    <param>%s</param>', $param);
        }
        $funcall.= sprintln('</funcall>');
    }
}

?>