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
                <img src="'.$contact->getPhoto('l').'"/>
            </div>';
            
        // Contact general infos
        $html .= '
            <div class="block">
                <h1 class="'.$presence['presence_txt'].'">'.$contact->getTrueName().'</h1><br />';

            if($this->testIsSet($contact->getData('name')))
                $html .= $contact->getData('name').' ';
            else
                $html .= $contact->getTrueName().' ';
                
            if($this->testIsSet($contact->getData('url')))
                $html .= '<br /><a target="_blank" href="'.$contact->getData('url').'">'.$contact->getData('url').'</a>';
            
        $html .= '<br /><br />
            </div>';
          
        if($this->testIsSet($presence['status'])) {
            $html .= '
                <div 
                    class="block" 
                    style="
                        max-height: 90px; 
                        min-height: auto; 
                        overflow: hidden; 
                        text-overflow: ellipsis;"
                    >';
                $html .= '
                    <div class="textbubble">
                        '.prepareString($presence['status']).'
                    </div>';
            $html .= '
                </div>';   
        }

        return $html;
	}
    
    function build()
    {
        $query = \Contact::query()->select()
                                   ->where(array(
                                           'jid' => $_GET['f']));
        $contact = \Contact::run_query($query);
        
        ?>
        <div id="contactsummary">
        <?php
        if(isset($contact[0])) {
            echo $this->prepareContactSummary($contact[0]);
        } 
        
        else {
            $contact = new Contact();
            echo $this->prepareContactSummary($contact);
        ?>
        <script type="text/javascript"><?php $this->callAjax('ajaxRefreshVcard', "'".$_GET['f']."'");?></script>
        <?php } ?>
        </div>
        <?php
    }
}
