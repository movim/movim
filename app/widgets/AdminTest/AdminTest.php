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
        $this->addjs('admintest.js');
        $this->addcss('admintest.css');
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
        $md = \modl\Modl::getInstance();
        $supported = $md->getSupportedDatabases();

        $cd = new \Modl\ConfigDAO();
        $config = $cd->get();

        $this->view->assign('dbconnected', $md->_connected);
        $this->view->assign('dbtype', $supported[$config->type]);
        $this->view->assign('dbinfos', sizeof($md->check()));
        $this->view->assign('websocketurl', $config->websocketurl);
    }
}
