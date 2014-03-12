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
 
class Subscribe extends WidgetBase {
    
    function load()
    {
        $this->addcss('subscribe.css');
        $this->addjs('subscribe.js');

        $xml = requestURL('http://movim.eu/server-vcards.xml', 1);
        if($xml) {
            $xml = simplexml_load_string($xml);
            
            $xml = (array)$xml->children();

            $this->view->assign('servers', $xml['vcard']);
        } else {
            $this->view->assign('servers', false);
        }
    }

    function flagPath($country) {
        return BASE_URI.'themes/movim/img/flags/'.strtolower($country).'.png';
    }

    function accountNext($server) {
        return Route::urlize('accountnext', array($server));
    }
}
