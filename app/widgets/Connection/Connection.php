<?php

/**
 * @package Widgets
 *
 * @file Connection.php
 * This file is part of MOVIM.
 * 
 * @version 1.0

 * Copyright (C)2013 MOVIM project
 * 
 * See COPYING for licensing information.
 */

class Connection extends WidgetBase
{
    function load()
    {
        $this->addcss('connection.css');
        $this->registerEvent('connection', 'onConnection');
    }
    
    function ajaxSetPresence()
    {

    }
    
    function onConnection($value)
    {
        if($value >= 10) {
            $value = floor(($value-10)/10);

            if($value == 0)
                RPC::call('movim_fill', 'countdown', '');
            else {
                RPC::call('movim_fill', 'countdown', $this->__('please_wait') . ' ' . $value);
            }
        } else
            RPC::commit();
    }
}

?>
