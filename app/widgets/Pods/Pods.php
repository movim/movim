<?php

/**
 * @package Widgets
 *
 * @file Pods.php
 * This file is part of Movim.
 *
 * @brief The Pods widget
 *
 * @author Jaussoin Timothée <edhelas@movim.eu>

 * Copyright (C)2014 Movim project
 *
 * See COPYING for licensing information.
 */
 
class Pods extends WidgetBase
{
    function load() {

    }

    function flagPath($country) {
        return BASE_URI.'themes/material/img/flags/'.strtolower($country).'.png';
    }

    function countryName($code) {
        $list = getCountries();
        $code = strtoupper($code);
        return $list[$code];
    }
    
    function display()
    {
        $json = requestURL(MOVIM_API.'pods', 1);
        $json = json_decode($json);
        
        if(is_object($json) && $json->status == 200) {
            $this->view->assign('pods', $json);
        }
    }
}
