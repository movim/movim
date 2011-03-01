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
		$this->registerEvent('rosterreceived', 'onRosterReceived');
		$this->registerEvent('incomeonline', 'onIncomingOnline');
		$this->registerEvent('incomeoffline', 'onIncomingOffline');
		$this->registerEvent('incomednd', 'onIncomingDND');
		$this->registerEvent('incomeaway', 'onIncomingAway');
    }

    function onRosterReceived($roster)
    {
    	$html = "<ul>";
    	$i = 0;
		foreach($roster["queryItemJid"] as $key => $value ) {
			if($value != "undefined") {
				if($roster["queryItemName"][$i] != NULL)
					$html .= "<li id='".$value."' onclick='setChatUser(\"".$value."\")'>".$roster["queryItemName"][$i]." : ".$value."</li>";
				else
					$html .= "<li id='".$value."' onclick='setChatUser(\"".$value."\")'>".$value."</li>";
			}
			$i++;
		}
		$html .= "</ul>";
        MovimRPC::call('movim_fill', 'tinylist', MovimRPC::cdata($html));
    }
    
	function onIncomingOnline($data)
	{
		list($jid, $place) = explode("/",$data['from']);
	    MovimRPC::call('incomingOnline',
                      MovimRPC::cdata($jid));
	}
		
	function onIncomingOffline($data)
	{
		list($jid, $place) = explode("/",$data['from']);
		
	    MovimRPC::call('incomingOffline', MovimRPC::cdata($jid));
	}
	
	function onIncomingDND($data)
	{
		list($jid, $place) = explode("/",$data['from']);
		
	    MovimRPC::call('incomingDND', MovimRPC::cdata($jid));
	}
	
	function onIncomingAway($data)
	{
		list($jid, $place) = explode("/",$data['from']);
		
	    MovimRPC::call('incomingAway', MovimRPC::cdata($jid));
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
          <h3><?php echo t('Contacts');?></h3>

          <div id="tinylist">
			<ul>
				<li class="online"><?php echo t('Online');?></li>
				<li class="offline"><?php echo t('Offline');?></li>
				<li class="hidden"><?php echo t('Hidden');?></li>
				<li class="away"><?php echo t('Away');?></li>
				<li class="dnd"><?php echo t('Do Not Disturb');?></li>
			</ul>
          </div>
  		  <input type="button" onclick="<?php $this->callAjax('ajaxRefreshRoster');?>"
         value="<?php echo t('Refresh Roster'); ?>" />
        </div>
        <?php
    }
}

?>
