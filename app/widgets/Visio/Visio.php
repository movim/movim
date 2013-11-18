<?php

/**
 * @package Widgets
 *
 * @file ChatExt.php
 * This file is part of MOVIM.
 * 
 * @brief A jabber chat widget.
 *
 * @author Guillaume Pasquet <etenil@etenilsrealm.nl>
 *
 * @version 1.0
 * @date 20 October 2010
 *
 * Copyright (C)2010 MOVIM project
 * 
 * See COPYING for licensing information.
 */
 
//require_once(APP_PATH . "widgets/ChatExt/ChatExt.php");

class Visio extends WidgetBase
{
	function WidgetLoad()
	{
    	$this->addcss('visio.css');
    	$this->addjs('visio.js');
    }
}
