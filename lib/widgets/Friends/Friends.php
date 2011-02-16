<?php

/**
 * @file Friends.php
 * This file is part of MOVIM.
 * 
 * @brief The Friends widget
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
    
    function WidgetLoad()
    {
    	$this->addcss('friends.css');
    	$this->addjs('friends.js');
		$this->registerEvent('vcardreceived', 'onVcardReceived');
		$this->registerEvent('rosterreceived', 'onRosterReceived');
		$this->registerEvent('incomeonline', 'onIncomingOnline');
		$this->registerEvent('incomeoffline', 'onIncomingOffline');
		$this->registerEvent('incomednd', 'onIncomingDND');
		$this->registerEvent('incomeaway', 'onIncomingAway');
    }

    function onVcardReceived($vcard)
    {
        $img = '<img alt="' . t("Your avatar") . '" src="data:'.
            $vcard['vCardPhotoType'] . ';base64,' . $vcard['vCardPhotoBinVal'] . '" />';
        $this->sendto('movim_fill', array('avatar', $this->cdata($img)));
    }

    function onRosterReceived($roster)
    {
    	$html = "<ul>";
    	$i = 0;
		foreach($roster["queryItemJid"] as $key => $value ) {
			if($value != "undefined") {
				if($roster["queryItemName"][$i] != NULL)
					$html .= "<li id='".$value."'>".$roster["queryItemName"][$i]." : ".$value."</li>";
				else
					$html .= "<li id='".$value."'>".$value."</li>";
			}
			$i++;
		}
		$html .= "</ul>";
        $this->sendto('movim_fill', array('tinylist', $this->cdata($html)));
    }
    
	function onIncomingOnline($data)
	{
		list($jid, $place) = explode("/",$data['from']);
		
	    $this->sendto('incomingOnline', array(
                          $this->cdata($jid)
                          ));
	}
		
	function onIncomingOffline($data)
	{
		list($jid, $place) = explode("/",$data['from']);
		
	    $this->sendto('incomingOffline', array(
                          $this->cdata($jid)
                          ));
	}
	
	function onIncomingDND($data)
	{
		list($jid, $place) = explode("/",$data['from']);
		
	    $this->sendto('incomingDND', array(
                          $this->cdata($jid)
                          ));
	}
	
	function onIncomingAway($data)
	{
		list($jid, $place) = explode("/",$data['from']);
		
	    $this->sendto('incomingAway', array(
                          $this->cdata($jid)
                          ));
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
                 onclick="<?php $this->callAjax('ajaxRefreshVcard', "'movim_drop'", "'drop'");?>"
                 value="Refresh vcard" />
          <h3><?php echo t('Contacts');?></h3>

          <div id="tinylist">
			<ul>
				<li class="online">Online</li>
				<li class="offline">Offline</li>
				<li class="hidden">Hidden</li>
				<li class="away">Away</li>
				<li class="dnd">Do Not Disturb</li>
			</ul>
          </div>
  		  <input type="button" onclick="<?php $this->callAjax('ajaxRefreshRoster', "'movim_drop'", "'drop'");?>"
         value="Refresh Roster" />
		 <div id="testzone"></div>
        </div>
        <?php
    }
}

?>
