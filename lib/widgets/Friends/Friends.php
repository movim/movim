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
		$this->registerEvent('vcardreceived', 'onVcardReceived');
    }

    function onVcardReceived($vcard)
    {
        $img = '<img alt="' . t("Your avatar") . '" src="data:'.
            $vcard['vCardPhotoType'] . ';base64,' . $vcard['vCardPhotoBinVal'] . '" />';
        $this->sendto('avatar', 'FILL', $img);
    }

    function processList($message)
    {
        $this->friendslist = $message;
    }

	function ajaxRefreshVcard()
	{
		$user = new User();
		$xmpp = XMPPConnect::getInstance($user->getLogin());
		$xmpp->getVCard(); // We send the vCard request
	}  

    function build()
    {
        ?>
        <div id="friends">
          <div id="avatar"></div>
          <br /><br /><br />
          <h3><?php echo t('Contacts');?></h3>
          <input type="button"
                 onclick="<?php $this->callAjax('ajaxRefreshVcard', 'FILL', "'testzone'");?>"
                 value="Refresh vcard" />
          <div id="tinylist">
			 <ul>
            </ul>
          </div>
		  <div id="testzone"></div>
        </div>
        <?php
        // We send a request to fetch the vcard straight away.
        //$user = new User();
        //$xmpp = XMPPConnect::getInstance($user->getLogin()); // We get the instance of the connexion
        //$xmpp->getVCard(); // We send the vCard request
    }
}

?>
