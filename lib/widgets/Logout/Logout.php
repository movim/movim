<?php

/**
 * @file Logout.php
 * This file is part of MOVIM.
 * 
 * @brief The little logout widget.
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

class Logout extends Widget
{
    
    function WidgetLoad()
    {
    	$this->addcss('logout.css');
    	$this->addjs('logout.js');
		$this->registerEvent('postdisconnected', 'onPostDisconnect');
        $this->registerEvent('serverdisconnect', 'onPostDisconnect'); // When you're kicked out
    }

    function onPostDisconnect($data)
    {
		$uri = str_replace("jajax.php", "", BASE_URI);
	    MovimRPC::call('pageLogout',
                       MovimRPC::cdata($uri."index.php?q=disconnect"));
    }

	function ajaxLogout()
	{
		$user = new User();
		$xmpp = XMPPConnect::getInstance($user->getLogin());
		$xmpp->logout();
	}

    function build()
    {
        ?>
        <div id="logout" onclick="<?php $this->callAjax('ajaxLogout');?>"><?php echo t('Logout'); ?></div>
        <?php
    }
}

?>
