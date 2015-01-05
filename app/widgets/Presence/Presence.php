<?php

/**
 * @package Widgets
 *
 * @file Logout.php
 * This file is part of MOVIM.
 * 
 * @brief The little logout widget.
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

use Moxl\Xec\Action\Presence\Chat;
use Moxl\Xec\Action\Presence\Away;
use Moxl\Xec\Action\Presence\DND;
use Moxl\Xec\Action\Presence\XA;
use Moxl\Xec\Action\Presence\Unavaiable;
use Moxl\Stanza\Stream;
use Moxl\Xec\Action\Storage\Get;

class Presence extends WidgetBase
{
    
    function load()
    {
        //$this->addcss('presence.css');
        $this->addjs('presence.js');
        $this->registerEvent('mypresence', 'onMyPresence');
    }
    
    function onMyPresence($packet)
    {
        $html = $this->preparePresence();
        RPC::call('movim_fill', 'presence_widget', $html);
        Notification::append(null, $this->__('status.updated'));
        RPC::call('setPresenceActions');
        RPC::call('movim_remove_class', '#presence_widget', 'unfolded');
    }

    function onPostDisconnect($data)
    {
        RPC::call('movim_reload',
                       BASE_URI."index.php?q=disconnect");
    }

    private function setPresence($show = false, $status = false)
    {
        Dialog::fill('');
        // We update the cache with our status and presence
        $presence = Cache::c('presence');

        if($show == false) $show = $presence['show'];
        if($status == false) $status = $presence['status'];

        if($presence == null || !isset($presence['show']) || $presence['show'] == '')
            $show = 'chat';

        if($presence == null|| !isset($presence['status']) || $presence['status'] == '')
            $status = 'Online with Movim';

        Cache::c(
            'presence', 
            array(
                'status' => $status,
                'show' => $show
                )
        );
        
        switch($show) {
            case 'chat':
                $p = new Chat;
                $p->setStatus($status)->request();
                break;               
            case 'away':             
                $p = new Away;       
                $p->setStatus($status)->request();
                break;               
            case 'dnd':              
                $p = new DND;        
                $p->setStatus($status)->request();
                break;               
            case 'xa':               
                $p = new XA;         
                $p->setStatus($status)->request();
                break;
        }
    }
    
    function ajaxSetPresence($show = false)
    {
        $this->setPresence($show, false);
    }

    function ajaxSetStatus($status)
    {
        $this->setPresence(false, $status);
    }
    
    function ajaxLogout()
    {
        $session = \Sessionx::start();
        $p = new Unavaiable;
        $p->setType('terminate')
          ->setRessource($session->ressource)
          ->setTo($this->user->getLogin())
          ->request();

        Stream::end();
    }

    function ajaxConfigGet() {
        $s = new Get;
        $s->setXmlns('movim:prefs')
          ->request();
    }

    // We get the server capabilities
    function ajaxServerCapsGet()
    {
        $session = \Sessionx::start();
        $c = new \Moxl\Xec\Action\Disco\Request;
        $c->setTo($session->host)
          ->request();
    }

        // We refresh the bookmarks
    function ajaxBookmarksGet()
    {
        $session = \Sessionx::start();
        $b = new \Moxl\Xec\Action\Bookmark\Get;
        $b->setTo($session->user.'@'.$session->host)
          ->request();
    }

    function ajaxOpenDialog()
    {
        Dialog::fill($this->preparePresenceList());
        RPC::call('setPresenceActions');
    }

    function preparePresence()
    {
        $cd = new \Modl\ContactDAO();
        $pd = new \Modl\PresenceDAO();
        
        $session = \Sessionx::start();
        $presence = $pd->getPresence($this->user->getLogin(), $session->ressource);

        $presencetpl = $this->tpl();
        
        $presencetpl->assign('me', $cd->get());
        $presencetpl->assign('presence', $presence);
        $presencetpl->assign('dialog',      $this->call('ajaxOpenDialog'));

        $html = $presencetpl->draw('_presence', true);

        return $html;
    }
    
    function preparePresenceList()
    {
        $txt = getPresences();
        $txts = getPresencesTxt();
    
        $session = \Sessionx::start();
        
        $pd = new \Modl\PresenceDAO();
        $p = $pd->getPresence($this->user->getLogin(), $session->ressource);

        $cd = new \Modl\ContactDAO();
        $contact = $cd->get($this->user->getLogin());
        if($contact == null) {
            $contact = new \Modl\Contact;
        }
       
        $presencetpl = $this->tpl();
    
        $presencetpl->assign('contact', $contact);
        $presencetpl->assign('p', $p);
        $presencetpl->assign('txt', $txt);
        $presencetpl->assign('txts', $txts);
        $presencetpl->assign('callchat',    $this->call('ajaxSetPresence', "'chat'"));
        $presencetpl->assign('callaway',    $this->call('ajaxSetPresence', "'away'"));
        $presencetpl->assign('calldnd',     $this->call('ajaxSetPresence', "'dnd'"));
        $presencetpl->assign('callxa',      $this->call('ajaxSetPresence', "'xa'"));

        $presencetpl->assign('calllogout',  $this->call('ajaxLogout'));
        $html = $presencetpl->draw('_presence_list', true);

        return $html;
    }

    function display()
    {
        $this->view->assign('presence', $this->preparePresence());
    }
}

?>
