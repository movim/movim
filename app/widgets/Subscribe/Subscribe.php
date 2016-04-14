<?php

/**
 * @package Widgets
 *
 * @file Subscribe.php
 * This file is part of MOVIM.
 *
 * @brief The account creation widget.
 *
 * @author Timothée Jaussoin <edhelas@gmail.com>
 *
 * @version 1.0
 * @date 25 November 2011
 *
 * Copyright (C)2010 MOVIM project
 *
 * See COPYING for licensing information.
 */

class Subscribe extends \Movim\Widget\Base {

    function load()
    {
        $this->addcss('subscribe.css');
        $this->addjs('subscribe.js');
    }

    function flagPath($country) {
        return BASE_URI.'themes/material/img/flags/'.strtolower($country).'.png';
    }

    function accountNext($server) {
        return Route::urlize('accountnext', array($server));
    }

    function display() {
        $json = requestURL(MOVIM_API.'servers', 1);
        $json = json_decode($json);

        $cd = new \Modl\ConfigDAO;
        $this->view->assign('config', $cd->get());

        if(is_object($json) && $json->status == 200) {
            $this->view->assign('servers', $json->servers);
        }
    }
}
