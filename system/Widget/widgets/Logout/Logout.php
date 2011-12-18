<?php

/**
 * @package Widgets
 *
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

class Logout extends WidgetBase
{
    
    function WidgetLoad()
    {
    	$this->addcss('logout.css');
		$this->registerEvent('postdisconnected', 'onPostDisconnect');
        $this->registerEvent('serverdisconnect', 'onPostDisconnect'); // When you're kicked out
    }

    function onPostDisconnect($data)
    {
	    RPC::call('movim_reload',
                       RPC::cdata(BASE_URI."index.php?q=disconnect"));
    }

	function ajaxLogout()
	{
		$user = new User();
		$xmpp = Jabber::getInstance($user->getLogin());
		$xmpp->logout();
	}

    function build()
    {
        ?>
        <div id="logout" onclick="<?php $this->callAjax('ajaxLogout');?> this.innerHTML = '<?php echo t('Disconnecting...'); ?>'"><?php echo t('Logout'); ?></div>
        <?php
    }
}

?>
