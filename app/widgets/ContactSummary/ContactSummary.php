<?php

/**
 * @package Widgets
 *
 * @file ContactSummary.php
 * This file is part of MOVIM.
 *
 * @brief Contact Summary widget
 *
 * @author Jaussoin Timothée <edhelas@movim.eu>
 *
 * Copyright (C)2014 MOVIM project
 *
 * See COPYING for licensing information.
 */

use Moxl\Xec\Action\Vcard\Get;

class ContactSummary extends WidgetCommon
{
    function load()
    {
        $this->addcss('contactsummary.css');
        $this->registerEvent('vcard_get_handle', 'onVcard');
    }
    
    function display()
    {
        $cd = new \Modl\ContactDAO();
        $contact = $cd->getRosterItem($_GET['f']);

        if(!isset($contact)) {
            $contact = $cd->get($_GET['f']);
        }
        
        if(isset($contact)) {
            $this->view->assign('contact', $contact);
        } else {
            $contact = new \Modl\Contact();
            $contact->jid = $_GET['f'];
            $this->view->assign('contact', $contact);
        }

        $this->view->assign('refresh', $this->genCallAjax('ajaxRefreshVcard', "'".$_GET['f']."'"));
    }
    
    function onVcard($packet)
    {
        $contact = $packet->content;

        // We try to get more informations on the contact
        $cd = new \Modl\ContactDAO();
        $contact_roster = $cd->getRosterItem($contact->jid);

        if(!isset($contact_roster)) {
            $contact_roster = $contact;
        }
        
        $html = $this->prepareContactSummary($contact_roster);
        RPC::call('movim_fill', 'contactsummary', $html);
    }
    
    function ajaxRefreshVcard($jid)
    {
        $r = new Get;
        $r->setTo(echapJid($jid))->request();
    }
    
    function prepareContactSummary($contact)
    {
        $csc = $this->tpl();
        $csc->assign('contact', $contact);
        return $csc->draw('_contactsummary_content', true);
    }
}
