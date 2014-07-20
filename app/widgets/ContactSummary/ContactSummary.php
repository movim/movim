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
    
    function display()
    {
        $cd = new \Modl\ContactDAO();

        if($_GET['f'] == $this->user->getLogin()) {
            $contact = $cd->get($this->user->getLogin());
        }

        if(!isset($contact)) {
            $contact = $cd->getRosterItem($_GET['f']);
        }

        if(!isset($contact)) {
            $contact = $cd->get($_GET['f']);
        }
        
        if(isset($contact)) {
            $this->view->assign('contact', $contact);
            $this->view->assign('refresh', false);
        } else {
            $contact = new modl\Contact();
            $contact->jid = $_GET['f'];
            $this->view->assign('contact', $contact);
            
            $this->view->assign('refresh', $this->genCallAjax('ajaxRefreshVcard', "'".$_GET['f']."'"));
        }
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
        // Contact avatar
        $html = '
            <a
                class="avatar"
                style="background-image: url('.$contact->getPhoto('l').');"
                href="'.Route::urlize('friend',$contact->jid).'">
            </a>
            ';
            
        $presencetxt = getPresencesTxt();
            
        // Contact general infos
        $html .= '<h1 class="paddedbottom">'.$contact->getTrueName().'</h1>';
                
        if($this->testIsSet($contact->url) && filter_var($contact->url, FILTER_VALIDATE_URL)) 
            $html .= '<a target="_blank" class="paddedtopbottom url" href="'.$contact->url.'">'.$contact->url.'</a>';
          
        if($contact->status) {
            $html .= '
                <div class="paddedbottom">
                    '.prepareString($contact->status).'
                </div>'; 
        }

        return $html;
    }
}
