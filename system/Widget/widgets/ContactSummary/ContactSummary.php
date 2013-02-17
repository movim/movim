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
        
        // Contact avatar
        $html .= '
            <div class="block avatar">
                <img src="'.$contact->getPhoto('l').'"/>
            </div>';
            
        $presencetxt = getPresencesTxt();
            
        // Contact general infos
        $html .= '
            <div class="block">
                <h1 class="'.$presencetxt[$contact->presence].'">'.$contact->getTrueName().'</h1><br />';

            if($this->testIsSet($contact->name))
                $html .= $contact->name.' ';
            else
                $html .= $contact->getTrueName().' ';
                
            if($this->testIsSet($contact->url))
                $html .= '<br /><a target="_blank" href="'.$contact->url.'">'.$contact->url.'</a>';
            
        $html .= '<br /><br />
            </div>';
          
        if($this->testIsSet($contact->status)) {
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
                        '.prepareString($contact->status).'
                    </div>';
            $html .= '
                </div>';   
        }

        return $html;
	}
    
    function build()
    {
        $cd = new modl\ContactDAO();
        $contact = $cd->getRosterItem($_GET['f']);
        
        ?>
        <div id="contactsummary">
        <?php
        if(isset($contact->photobin)) {
            echo $this->prepareContactSummary($contact);
        } 
        
        else {
            $contact = new modl\Contact();
            echo $this->prepareContactSummary($contact);
        ?>
        <script type="text/javascript"><?php $this->callAjax('ajaxRefreshVcard', "'".$_GET['f']."'");?></script>
        <?php } ?>
        </div>
        <?php
    }
}
