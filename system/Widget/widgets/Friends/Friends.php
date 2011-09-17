<?php

/**
 * @package Widgets
 *
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
		
		$this->registerEvent('incomepresence', 'onIncomingPresence');
    }

    function onRosterReceived($roster)
    {
		$html = $this->prepareRoster($roster);
        RPC::call('movim_fill', 'tinylist', RPC::cdata($html));
    }

    function prepareRoster($roster) {
        if(!$roster)
            $html = '<script type="text/javascript">'.$this->genCallAjax('ajaxRefreshRoster').'</script>';
        $html .= "<ul>";
    	$i = 0;

        // Is there anything in the roster?
        if(!is_array($roster) || count($roster) < 1) {
            $html .= "</ul>";
            return $html;
        }

        $session = Session::start(APP_NAME);
        $presences = $session->get('presences');
        
		foreach($roster["queryItemJid"] as $key => $value ) { // We see each contact
			if($value != "undefined") {
			    
			    $status = (isset($presences[$value]['status'])) 
			        ? $presences[$value]['status'] 
			        : $value;
                
                if(is_array($presences[$value])) {
                    unset($presences[$value]['status']);
                    $rank = min($presences[$value]);
                    
                    switch ($rank) {
                        case 1:
					        $presence = "online";
					        break;
                        case 2:
				        	$presence = "dnd";
				        	break;
                        case 3:
					        $presence = "away";
					        break;
                        case 4:
					        $presence = "offline";
					        break;
                        case 5:
					        $presence = "away";
					        break;
				        default:
				        	$presence = "offline";
                    }
                } else {
                    $presence = "offline";
                }
                             
				$html .= '<li 
				            id="'.$value.'" 
				            title="'.$status.'" 
				            class="'.$presence.'"
				          >';
					
					$c = new ContactHandler();
	                $contact = $c->get($value);
	                
					$cachevcard = Cache::c('vcard'.$value); // We try to load the Vcard
					//$html .= "<a class='user_page' href='?q=friend&f=".$value."'><img class='avatar' src='data:".	$cachevcard['vCardPhotoType'] . ";base64," . $cachevcard['vCardPhotoBinVal'] . "' />"
					//		."</a>"; // Draw the avatar
					$html .= "<a class='user_page' href='?q=friend&f=".$value."'><img class='avatar' src='".$contact->getPhoto()."' />"
							."</a>";
					
                    $html .= '<span onclick="'.$this->genCallWidget("Chat","ajaxOpenTalk", "'".$value."'").'">';
					// We try to display an understadable name

                    /*$name = $cachevcard['vCardFN'].' '.$cachevcard['vCardFamily'];
                    
                    if($name == " ")
                        $name = $cachevcard['vCardNickname'];
                    if($name == "")
                        $name = $cachevcard['vCardNGiven'];
                    if($name == "")
                        $name = $roster["queryItemName"][$i];
                    if($name == "")
                        $name = $cachevcard['from'];*/
                    
                    $html .= $contact->getTrueName();
					$html .= '</span>';	
					//$html .= '</span>
					//			<span class="status" id="status_'.$value.'" title="'.$status.'">'.$status.'</span></li>';
				
				$html .= "
				</li>";
			}
			$i++;
		}
		$html .= "</ul>";
		return $html;
    }
    
    function onIncomingPresence($data)
    {
		list($jid, $place) = explode("/",$data['from']);
	    RPC::call('incomingPresence',
                      RPC::cdata($jid), RPC::cdata($data['status']));
    }

	function onIncomingOnline($data)
	{
		list($jid, $place) = explode("/",$data['from']);
	    RPC::call('incomingOnline', RPC::cdata($jid));
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
		$xmpp = Jabber::getInstance();
		$xmpp->getRosterList();
	}

    function build()
    { 
        ?>
        <div id="friends">
            <div id="tinylist">
                <?php echo $this->prepareRoster(Cache::c('roster')); ?>
            </div>

            <div class="config_button" onclick="<?php $this->callAjax('ajaxRefreshRoster');?>"></div>
        </div>
        <?php
    }
}

?>
