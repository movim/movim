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
 
require_once(APP_PATH . "widgets/ChatExt/ChatExt.php");

class ChatPop extends WidgetBase
{
	function WidgetLoad()
	{
    	$this->addcss('chatpop.css');
    	$this->addjs('chatpop.js');
    }
    
    function build()
    {
        $chatext = new ChatExt();
        
        echo '<div id="chatpop">';
            echo $chatext->preparePop();
        echo '</div>';
        ?>
        <div id="connection">
            <?php echo t('Connection').'...'; ?>
        </div>
        <?php
    }
    
}
