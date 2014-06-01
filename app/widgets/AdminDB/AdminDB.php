<?php

/**
 * @package Widgets
 *
 * @file AdminDB.php
 * This file is part of Movim.
 *
 * @brief The DB Administration widget
 *
 * @author Jaussoin Timothée <edhelas@movim.eu>

 * Copyright (C)2014 Movim project
 *
 * See COPYING for licensing information.
 */
 
class AdminDB extends WidgetBase
{
    function load() {

    }

    function display()
    {
        $md = \modl\Modl::getInstance();
        $infos = $md->check();
        
        $errors = '';

        $this->view->assign('infos', $infos); 
        $this->view->assign('db_update', $this->genCallAjax('ajaxUpdateDatabase')
            ."this.className='button color orange icon loading'; setTimeout(function() {location.reload(false)}, 1000);");
        try {
            $md->connect();
        } catch(Exception $e) {
            $errors = $e->getMessage();
        }

        if(file_exists(DOCUMENT_ROOT.'/config/db.ini')) {
            $conf = parse_ini_file(DOCUMENT_ROOT.'/config/db.ini');
        }

        $supported = $md->getSupportedDatabases();
        
        $this->view->assign('connected', $md->_connected);
        $this->view->assign('validatebutton', $this->_validatebutton);
        $this->view->assign('conf', $conf);
        $this->view->assign('dbtype', $supported[$conf['type']]);
        $this->view->assign('errors', $errors);
    }
}
