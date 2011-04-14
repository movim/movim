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

class Friends extends WidgetBase
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
		$html = $this->prepareRoster($roster);
        RPC::call('movim_fill', 'tinylist', RPC::cdata($html));
    }

    function prepareRoster($roster) {
        $html = "<ul>";
    	$i = 0;

        // Is there anything in the roster?
        if(!is_array($roster) || count($roster) < 1) {
            $html .= "</ul>";
            return $html;
        }

		foreach($roster["queryItemJid"] as $key => $value ) { // We see each contact
			if($value != "undefined") {
				$html .= "<li id='".$value."' onclick='setChatUser(\"".$value."\")' title='".$value."'>";
				
				if($roster["queryItemName"][$i] != NULL) { // If we can get the name
					$cachevcard = Cache::c('vcard'.$value); // We try to load the Vcard
					$html .= "<img class='avatar' src='data:".	$cachevcard['vCardPhotoType'] . ";base64," . $cachevcard['vCardPhotoBinVal'] . "' />"
							."<span class='status'></span>"
							."<a class='user_page' href='?q=friend&f=".$value."'></a>"; // Draw the avatar
								
					// We try to display an understadable name
					if(isset($cachevcard['vCardFN']) || isset($cachevcard['vCardFamily']))
						$html .= $cachevcard['vCardFN'] ." ".$cachevcard['vCardFamily'];
					elseif(isset($cachevcard['vCardNickname']))
						$html .= $cachevcard['vCardNickname'];
					else 
						$html .= $roster["queryItemName"][$i];
						
					$html .= "</li>";
				} else
					$html .= $value;
					
				$html .= "</li>";
			}
			$i++;
		}
		$html .= "</ul>";
		return $html;
    }

	function onIncomingOnline($data)
	{
		list($jid, $place) = explode("/",$data['from']);
	    RPC::call('incomingOnline',
                      RPC::cdata($jid));
	}

	function onIncomingOffline($data)
	{
		list($jid, $place) = explode("/",$data['from']);

	    RPC::call('incomingOffline', RPC::cdata($jid));
	}

	function onIncomingDND($data)
	{
		list($jid, $place) = explode("/",$data['from']);

	    RPC::call('incomingDND', RPC::cdata($jid));
	}

	function onIncomingAway($data)
	{
		list($jid, $place) = explode("/",$data['from']);

	    RPC::call('incomingAway', RPC::cdata($jid));
	}

	function ajaxRefreshRoster()
	{
		$user = new User();
		$xmpp = Jabber::getInstance($user->getLogin());
		$xmpp->getRosterList();
	}

    function build()
    {
        ?>
        <div id="friends">
          <div class="config_button" onclick="<?php $this->callAjax('ajaxRefreshRoster');?>"></div>
          <h3><?php echo t('Contacts');?></h3>

          <div id="tinylist">
          	<?php echo $this->prepareRoster(Cache::c('roster')); ?>
          </div>
        </div>
        <?php
    }
}

?>
