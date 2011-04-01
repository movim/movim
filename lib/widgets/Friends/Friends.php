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

class Friends extends GuiWidget
{

    function GuiWidgetLoad()
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
        MovimRPC::call('movim_fill', 'tinylist', MovimRPC::cdata($html));
    }

    function prepareRoster($roster) {
        $html = "<ul>";
    	$i = 0;

        // Is there anything in the roster?
        if(!is_array($roster) || count($roster) < 1) {
            $html .= "</ul>";
            return $html;
        }

		foreach($roster["queryItemJid"] as $key => $value ) {
			if($value != "undefined") {
				if($roster["queryItemName"][$i] != NULL) {
					$html .= "<li id='".$value."' onclick='setChatUser(\"".$value."\")'>";
					$html .= "<a class='user_page' href='?q=friend&f=".$value."'></a>";
					$html .= $roster["queryItemName"][$i]." : ".$value."</li>";
				} else
					$html .= "<li id='".$value."' onclick='setChatUser(\"".$value."\")'>".$value."</li>";
			}
			$i++;
		}
		$html .= "</ul>";
		return $html;
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
          <div class="config_button" onclick="<?php $this->callAjax('ajaxRefreshRoster');?>"></div>
          <h3><?php echo t('Contacts');?></h3>

          <div id="tinylist">
          	<?php echo $this->prepareRoster(Cache::handle('roster')); ?>
          </div>
        </div>
        <?php
    }
}

?>
