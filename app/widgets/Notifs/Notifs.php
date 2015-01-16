<?php

/**
 * @package Widgets
 *
 * @file Notifs.php
 * This file is part of MOVIM.
 *
 * @brief The notification widget
 *
 * @author Timothée Jaussoin <edhelas@gmail.com>
 *
 * @version 1.0
 * @date 16 juin 2011
 *
 * Copyright (C)2010 MOVIM project
 *
 * See COPYING for licensing information.
 */
use Moxl\Xec\Action\Presence\Subscribed;
use Moxl\Xec\Action\Presence\Unsubscribed;
use Moxl\Xec\Action\Roster\AddItem;
use Moxl\Xec\Action\Presence\Subscribe;

class Notifs extends WidgetCommon
{
    function load()
    {
        $this->addcss('notifs.css');
        $this->addjs('notifs.js');

        $this->registerEvent('subscribe', 'onNotifs');
        $this->registerEvent('roster_additem_handle', 'onNotifs');
        $this->registerEvent('presence_subscribe_handle', 'onNotifs');
        $this->registerEvent('presence_subscribed_handle', 'onNotifs');
    }

    function onNotifs($packet = false)
    {
        $html = $this->prepareNotifs();
        RPC::call('movim_fill', 'notifs_widget', $html);
    }

    /*
     * Create the list of notifications
     * @return string
     */  
    function prepareNotifs()
    {
        $cd = new \Modl\ContactDAO();
        $contacts = $cd->getRosterFrom();

        $invitations = array();

        $notifs = \Cache::c('activenotifs');
        if(is_array($notifs)) {
            foreach($notifs as $key => $value) {
                array_push($invitations, $cd->get($key));
            }
        }

        $nft = $this->tpl();

        $nft->assign('invitations', $invitations);
        $nft->assign('contacts', $contacts);
        return $nft->draw('_notifs_from', true);
    }

    function ajaxAccept($jid) {
        $jid = echapJid($jid);
        
        $r = new AddItem;
        $r->setTo($jid)
          ->setFrom($this->user->getLogin())
          ->request();

        $p = new Subscribe;
        $p->setTo($jid)
          ->request();

        $p = new Subscribed;
        $p->setTo($jid)
          ->request();

        // TODO : move in Moxl
        $notifs = Cache::c('activenotifs');

        unset($notifs[$jid]);
        
        Cache::c('activenotifs', $notifs);
    }

    function ajaxRefuse($jid) {
        $jid = echapJid($jid);
        
        $p = new Unsubscribed;
        $p->setTo($jid)
          ->request();

        // TODO : move in Moxl
        $notifs = Cache::c('activenotifs');

        unset($notifs[$jid]);
        
        Cache::c('activenotifs', $notifs);

        $this->onNotifs();
    }

    function genCallAccept($jid) {
        return $this->call('ajaxAccept', "'".$jid."'");
    }

    function genCallRefuse($jid) {
        return $this->call('ajaxRefuse', "'".$jid."'");
    }
}
