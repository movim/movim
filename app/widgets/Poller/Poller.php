<?php

/**
 * @package Widgets
 *
 * @file Chat.php
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

class Poller extends WidgetBase
{
	function WidgetLoad()
	{
        // We add the javascript that does the job.
        $this->addjs('poller.js');
        // And that's it!
	}

    function build()
    {
    }
}

?>
