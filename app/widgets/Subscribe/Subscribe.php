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
    
    function WidgetLoad()
    {
        $this->addcss('subscribe.css');
        $this->addjs('subscribe.js');

        $xml = simplexml_load_string(file_get_contents('http://movim.eu/server-vcards.xml'));
        $xml = (array)$xml->children();

        $this->view->assign('servers', $xml['vcard']);
    }

    function flagPath($country) {
        return BASE_URI.'themes/movim/img/flags/'.strtolower($country).'.png';
    }

    function accountNext($server) {
        return Route::urlize('accountnext', array($server));
    }
}
