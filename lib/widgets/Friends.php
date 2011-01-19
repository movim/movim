<?php

/**
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

class Friends extends Widget
{
    private $friendslist;
    
    function WidgetLoad()
    {
        $this->addjs("test.js");

		// Registering event handlers.
		$this->registerEvent('vcardreceived', 'onVcardReceived');
    }

    function processList($message)
    {
        $this->friendslist = $message;
    }

	function ajaxRefreshVcard()
	{
		echo date('Y-m-d H:i:s') . '<br />';
	
		echo "TOTO!!!!";

		$user = new User();
		$xmpp = XMPPConnect::getInstance($user->getLogin());
		$xmpp->getVCard(); // We send the vCard request
	}    
    function build()
    {
        ?>
        <div id="friends">
          <img src="" alt="<?php echo t('Your avatar');?>">
          <br /><br /><br />
          <h3><?php echo t('Contacts');?></h3>
          <input type="button"
                 onclick="<?php $this->callAjax('ajaxRefreshVcard', 'FILL', "'testzone'");?>"
                 value="Refresh vcard" />
          <div id='tinylist'>
			 <ul>
            </ul>
          </div>
		  <div id="testzone"></div>
        </div>
        <?php
        // We send a request to fetch the vcard straight away.
        $user = new User();
        $xmpp = XMPPConnect::getInstance($user->getLogin()); // We get the instance of the connexion
        $xmpp->getVCard(); // We send the vCard request
    }

    function onVcardReceived($vcard)
    {
		echo "vcard received: " .substr(var_export($vcard, true), 0, 20);
    }

}

?>
