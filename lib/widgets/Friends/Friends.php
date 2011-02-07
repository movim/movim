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
    	$this->addcss('friends.css');
		$this->registerEvent('vcardreceived', 'onVcardReceived');
		$this->registerEvent('rosterreceived', 'onRosterReceived');
    }

    function onVcardReceived($vcard)
    {
        $img = '<img alt="' . t("Your avatar") . '" src="data:'.
            $vcard['vCardPhotoType'] . ';base64,' . $vcard['vCardPhotoBinVal'] . '" />';
        $this->sendto('avatar', 'FILL', $img);
    }

    function onRosterReceived($roster)
    {
    	$html = "<ul>";
    	$i = 0;
		foreach($roster["queryItemJid"] as $key => $value ) {
			if($value != "undefined") {
				if($roster["queryItemName"][$i] != NULL)
					$html .= "<li>".$roster["queryItemName"][$i]." : ".$value."</li>";
				else
					$html .= "<li>".$value."</li>";
			}
			$i++;
		}
		$html .= "</ul>";
        $this->sendto('tinylist', 'FILL', $html);
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
	
	function ajaxRefreshRoster()
	{
		$user = new User();
		$xmpp = XMPPConnect::getInstance($user->getLogin());
		$xmpp->getRosterList();
	}  

    function build()
    {
        ?>
        <div id="friends">
          <div id="drop" style="display: none;"></div>
          <div id="avatar"></div>
		  <input type="button"
                 onclick="<?php $this->callAjax('ajaxRefreshVcard', 'FILL', "'drop'");?>"
                 value="Refresh vcard" />
          <h3><?php echo t('Contacts');?></h3>

          <div id="tinylist">
			<ul><li class="online">test</li><li class="offline">test</li><li class="away">test</li><li class="busy">test</li></ul>
          </div>
  		  <input type="button"
         onclick="<?php $this->callAjax('ajaxRefreshRoster', 'FILL', "'drop'");?>"
         value="Refresh Roster" />
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
