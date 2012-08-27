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

class ContactSummary extends WidgetCommon
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
        $r = new moxl\VcardGet();
        $r->setTo($jid)->request();
	}
    
    function ajaxRemoveContact($jid) {
		if(checkJid($jid)) {            
            $r = new moxl\RosterRemoveItem();
            $r->setTo($jid)
              ->request();
            
			$p = new moxl\PresenceUnsubscribe();
            $p->setTo($jid)
              ->request();
		} else {
			throw new MovimException("Incorrect JID `$jid'");
		}

        global $sdb;
        $contact = $sdb->select('Contact', array('key' => $this->user->getLogin(), 'jid' => $jid));
        $sdb->delete($contact[0]);
    }
	
	function prepareContactSummary($contact)
	{
        $gender = getGender();
        $marital = getMarital();
        
        $query = \Presence::query()->select()
                           ->where(array(
                                   'key' => $this->user->getLogin(),
                                   'jid' => $contact->getData('jid')))
                           ->limit(0, 1);
        $data = \Presence::run_query($query);
        
        if(isset($data[0]))
            $presence = $data[0]->getPresence();
        
        // Contact avatar
        $html .= '
            <div class="block avatar">
                <img src="'.$contact->getPhoto().'"/>
            </div>';
            
        // Contact general infos
        $html .= '
            <div class="block">
                <h1 class="'.$presence['presence_txt'].'">'.$contact->getTrueName().'</h1><br />';

            if($this->testIsSet($contact->getData('name')))
                $html .= $contact->getData('name').' ';
            else
                $html .= $contact->getTrueName().' ';
                
            if($contact->getData('gender') != 'N' && $this->testIsSet($contact->getData('gender')))
                $html .= '<br /><span>'.t('Gender').'</span>'.$gender[$contact->getData('gender')].' ';
                
            if($contact->getData('marital') != 'none' && $this->testIsSet($contact->getData('marital')))
                $html .= $marital[$contact->getData('marital')].' ';
                
            if($contact->getData('date') != '0000-00-00' && $this->testIsSet($contact->getData('date')))
                $html .= '<span>'.t('Date of Birth').'</span>'.date('j M Y',strtotime($contact->getData('date'))).' ';
                
            if($this->testIsSet($contact->getData('jid')))
                $html .= $contact->getData('jid').' ';
                
            if($this->testIsSet($contact->getData('url')))
                $html .= '<br /><a target="_blank" href="'.$contact->getData('url').'">'.$contact->getData('url').'</a>';
            
        $html .= '<br /><br />
            </div>';
          
        if($this->testIsSet($presence['status'])) {
            $html .= '
                <div class="block">';
                $html .= '
                    <div class="textbubble">
                        '.prepareString($presence['status']).'
                    </div>';
            $html .= '
                </div>';   
        }
        
        if($contact->getData('vcardreceived') != 1)
            $html .= '<script type="text/javascript">setTimeout(\''.$this->genCallAjax('ajaxRefreshVcard', '"'.$contact->getData('jid').'"').'\', 2000);</script>';

        return $html;
	}
    
    function build()
    {
        $query = \Contact::query()->select()
                                   ->where(array(
                                           'key' => $this->user->getLogin(),
                                           'jid' => $_GET['f']));
        $contact = \Contact::run_query($query);
        ?>
        <div id="contactsummary">
        <?php
        if(isset($contact[0])) {
            echo $this->prepareContactSummary($contact[0]);
        /*?>
        <div class="config_button" onclick="<?php $this->callAjax('ajaxRefreshVcard', "'".$contact[0]->getData('jid')."'");?>"></div>
        <?php*/ } 
        
        else {
        ?>
        <script type="text/javascript"><?php $this->callAjax('ajaxRefreshVcard', "'".$_GET['f']."'");?></script>
        <?php } ?>
        </div>
        <?php
    }
}
