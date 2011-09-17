<?php

/**
 * @package Widgets
 *
 * @file Roster.php
 * This file is part of MOVIM.
 *
 * @brief The Roster widget
 *
 * @author Jaussoin Timothée <edhelas@gmail.com>
 *
 * @version 1.0
 * @date 30 August 2010
 *
 * Copyright (C)2010 MOVIM project
 *
 * See COPYING for licensing information.
 */

class Roster extends WidgetBase
{

    function WidgetLoad()
    {
    	$this->addcss('roster.css');
    	$this->addjs('roster.js');
		$this->registerEvent('roster', 'onRoster');
		$this->registerEvent('presence', 'onPresence');
		$this->registerEvent('vcard', 'onVcard');
    }
    
	function onPresence($presence)
	{
	    $arr = $presence->getPresence();
	    $tab = PresenceHandler::getPresence($arr['jid'], true);
	    RPC::call('incomingPresence',
                      RPC::cdata($tab['jid']), RPC::cdata($tab['presence_txt']));
	}
	
    function onVcard($contact)
    {
        $html = $this->prepareRosterElement($contact, true);
        RPC::call('movim_fill', 'roster'.$contact->getData('jid'), RPC::cdata($html));
    }
	
    function onRoster($roster)
    {
		$html = $this->prepareRoster();
        RPC::call('movim_fill', 'rosterlist', RPC::cdata($html));
    }
    
	function ajaxRefreshRoster()
	{
		$xmpp = Jabber::getInstance();
		$xmpp->getRosterList();
	}
	
	function prepareRosterElement($contact, $inner = false)
	{
        $presence = PresenceHandler::getPresence($contact->getData('jid'), true);
        $start = 
            '<li 
                class="';
                    if(isset($presence['presence']))
                        $start .= ''.$presence['presence_txt'].' ';
                    else
                        $start .= 'offline ';
                        
                    if($contact->getData('jid') == $_GET['f'])
                        $start .= 'active ';
        $start .= '" 
                id="roster'.$contact->getData('jid').'" 
             >';
        $middle = '<div class="chat ';
            if(isset($presence) && $presence["presence_txt"] != 'offline') {
                $middle .= 'on" onclick="'.$this->genCallWidget("Chat","ajaxOpenTalk", "'".$contact->getData('jid')."'");
            }
        $middle .= '"></div>
                 <a ';
            $middle .= 
                     'title="'.$contact->getTrueName().' ('.$contact->getData('jid').')" 
                     href="?q=friend&f='.$contact->getData('jid').'"
                 >
                    <img class="avatar" src="'.$contact->getPhoto().'" />'.
                    '<span>'.$contact->getTrueName().'</span>
                 </a>';
        $end = '</li>';
        //var_dump($presence);
        if($inner == true)
            return $middle;
        else
            return $start.$middle.$end;
	}
	
	function prepareRoster()
	{
        global $sdb;
        $user = new User();
        $contact = $sdb->select('Contact', array('key' => $user->getLogin())); 
        
        $html = '';

            if($contact != false) {
                foreach($contact as $c) {
                    $html .= $this->prepareRosterElement($c);
                }
                $html .= '<li class="more" onclick="showRoster(this);"><a href="#"><span>'.t('Show All').'</span></a></li>';
            } else {
                $html .= '<script type="text/javascript">'.$this->genCallAjax('ajaxRefreshRoster').'</script>';
            }

        return $html;
	}
    
    function build()
    { 
    ?>
        <div id="roster">
            <ul id="rosterlist">
            <?php echo $this->prepareRoster(); ?>
            </ul>
            <div class="config_button" onclick="<?php $this->callAjax('ajaxRefreshRoster');?>"></div>
            <script type="text/javascript">sortRoster();</script>
        </div>
    <?php
    }
}

?>
