<?php

/**
 * @package Widgets
 *
 * @file AdminTest.php
 * This file is part of Movim.
 *
 * @brief The Admin Pest part
 *
 * @author Jaussoin Timothée <edhelas@movim.eu>

 * Copyright (C)2014 Movim project
 *
 * See COPYING for licensing information.
 */
 
class AdminTest extends WidgetBase
{
    function load() {

    }

    public function valid($what)
    {
        if($what)
            return "message success";
        else
            return "message error";
    }

    public function version()
    {
        return (version_compare(PHP_VERSION, '5.3.0') >= 0);
    }

    public function testDir($dir)
    {
        return (file_exists($dir) && is_dir($dir) && is_writable($dir));
    }
    
    public function testFile($file)
    {
        return (file_exists($file) && is_writable($file));
    }

    function display()
    {

    }
}
