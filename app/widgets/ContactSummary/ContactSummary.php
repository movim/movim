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

use Moxl\Xec\Action\Vcard\Get;

class ContactSummary extends WidgetCommon
{
    function load()
    {
        $this->addcss('contactsummary.css');
        $this->registerEvent('vcard', 'onVcard');
    }
    
    function onVcard($contact)
    {
        $html = $this->prepareContactSummary($contact);
        RPC::call('movim_fill', 'contactsummary', $html);
    }
    
    function ajaxRefreshVcard($jid)
    {
        $r = new Get;
        $r->setTo(echapJid($jid))->request();
    }
    
    function prepareContactSummary($contact)
    {
        $gender = getGender();
        $marital = getMarital();
        
        // Contact avatar
        $html = '
            <img class="avatar" src="'.$contact->getPhoto('l').'"/>
            ';
            
        $presencetxt = getPresencesTxt();
            
        // Contact general infos
        
        if(isset($contact->presence))
            $html .= '
                <h1 class="'.$presencetxt[$contact->presence].'">'.$contact->getTrueName().'</h1>';
        else
            $html .= '<h1>'.$contact->getTrueName().'</h1>';
                
        if($this->testIsSet($contact->url) && filter_var($contact->url, FILTER_VALIDATE_URL)) 
            $html .= '<a target="_blank" href="'.$contact->url.'">'.$contact->url.'</a>';
          
        if(isset($contact->status)) {
            $html .= '
                <div class="textbubble">
                    '.prepareString($contact->status).'
                </div>'; 
        }

        return $html;
    }
    
    function build()
    {
        $cd = new \Modl\ContactDAO();
        
        if($_GET['f'] == $this->user->getLogin()) {
            $contact = $cd->get($this->user->getLogin());
        }
        
        if(!isset($contact)) {
            $contact = $cd->get($_GET['f']);
        }
        ?>
        <div id="contactsummary">
        <?php
        if($contact != null) {
            echo $this->prepareContactSummary($contact);
        } else {
            $contact = new modl\Contact();
            echo $this->prepareContactSummary($contact);
            ?>
            <script type="text/javascript">
                setTimeout("<?php $this->callAjax('ajaxRefreshVcard', "'".$_GET['f']."'");?>", 1000);
            </script>
        <?php } ?>
        </div>
        <?php
    }
}
