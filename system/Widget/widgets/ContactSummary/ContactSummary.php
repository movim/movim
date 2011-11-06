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

class ContactSummary extends WidgetBase
{

    function WidgetLoad()
    {
    	$this->addcss('contactsummary.css');
		$this->registerEvent('vcard', 'onVcard');
    }
    
    function onVcard($contact)
    {
        $html = $this->prepareContactSummary($contact);
        RPC::call('movim_fill', 'contactsummary', RPC::cdata($html));
    }
    
	function ajaxRefreshVcard($jid)
	{
		$xmpp = Jabber::getInstance();
		$xmpp->getVCard($jid);
	}
	
	function prepareContactSummary($contact)
	{
        $presence = PresenceHandler::getPresence($contact->getData('jid'), true);
        $html ='<h1>'.$contact->getTrueName().'</h1><img src="'.$contact->getPhoto().'"/>';
        
        if($contact->getData('vcardreceived') != 1)
            $html .= '<script type="text/javascript">setTimeout(\''.$this->genCallAjax('ajaxRefreshVcard', '"'.$contact->getData('jid').'"').'\', 500);</script>';
            
        if($presence != NULL)
            $html .= '<div id="status">'.$presence['status'].'</div>';
        return $html;
	}
    
    function build()
    {
        global $sdb;
        $user = new User();
        $contact = $sdb->select('Contact', array('key' => $user->getLogin(), 'jid' => $_GET['f']));
        ?>
        <div id="contactsummary">
        <?php
        if(isset($contact[0])) {
            echo $this->prepareContactSummary($contact[0]);
        ?>
        <div class="config_button" onclick="<?php $this->callAjax('ajaxRefreshVcard', "'".$contact[0]->getData('jid')."'");?>"></div>
        <?php } ?>
        </div>
        <?php
    }
}
