<?php

/**
 * @package Widgets
 *
 * @file Notifs.php
 * This file is part of MOVIM.
 *
 * @brief The notification widget
 *
 * @author Timothée Jaussoin <edhelas@gmail.com>
 *
 * @version 1.0
 * @date 16 juin 2011
 *
 * Copyright (C)2010 MOVIM project
 *
 * See COPYING for licensing information.
 */

class Tabs extends WidgetBase
{
    function WidgetLoad()
    {
    	$this->addcss('tabs.css');
    	$this->addjs('tabs.js');
    }
    
    function build() {  
    ?>
    <ul id="navtabs">

    </ul>

    <?php    
    }
}
