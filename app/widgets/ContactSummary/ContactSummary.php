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
        RPC::call('movim_fill', 'contactsummary', $html);
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
        $html = '
            <img class="avatar" src="'.$contact->getPhoto('l').'"/>
            ';
            
        $presencetxt = getPresencesTxt();
            
        // Contact general infos
        
        if(isset($contact->presence))
            $html .= '
                <h1 class="'.$presencetxt[$contact->presence].'">'.$contact->getTrueName().'</h1>';

            /*if($this->testIsSet($contact->name))
                $html .= $contact->name.' ';
            else*/
                $html .= $contact->getTrueName().' ';
                
            if($this->testIsSet($contact->url) && filter_var($contact->url, FILTER_VALIDATE_URL)) 
                $html .= '<br /><a target="_blank" href="'.$contact->url.'">'.$contact->url.'</a>';
          
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
        $cd = new modl\ContactDAO();
        
        if($_GET['f'] == $this->user->getLogin()) {
            $contact = $cd->get($this->user->getLogin());
        } /*else {
            $contact = $cd->getRosterItem($_GET['f']);
            $refresh = true;
        }*/
        
        if(!isset($contact)) {
            $contact = $cd->get($_GET['f']);
            //$refresh = false;
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
