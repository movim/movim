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
        $this->registerEvent('notification', 'onNotification');
        $this->registerEvent('notificationdelete', 'onNotificationDelete');
        $this->registerEvent('notifications', 'displayNotifications');
        $this->registerEvent('nonotification', 'onNoNotification');
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
        foreach(\Cache::c('activenotifs') as $key => $value) {
            array_push($invitations, $cd->get($key));
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
        $p->setTo(echapJid($jid))
          ->request();
          
        $notifs = Cache::c('activenotifs');

        unset($notifs[$jid]);
        
        Cache::c('activenotifs', $notifs);
        
        RPC::call('movim_fill', 'notifs', $this->prepareNotifs());
    }

    function ajaxRefuse($jid) {
        $jid = echapJid($jid);
        $p = new Unsubscribed;
        $p->setTo($jid)
          ->request();
        
        $notifs = Cache::c('activenotifs');

        unset($notifs[$jid]);
        
        Cache::c('activenotifs', $notifs);
        
        RPC::call('movim_fill', 'notifs', $this->prepareNotifs());
    }
    
    /*function ajaxSubscribe($jid) {
        $jid = echapJid($jid);

        
        RPC::commit();
    }*/
    /*function prepareNotifs()
     *     {$c->prepareNotifs()}
    {
        $notifsnum = 0;
              
        $html = '
            <div id="notifslist">
                <ul>';
                
            // XMPP notifications
            $notifs = Cache::c('activenotifs');

            if($notifs == null)
                $notifs = array();
            
            
            if(sizeof($notifs) != 0) {
                foreach($notifs as $n => $val) {
                    if($val == 'sub')
                        $html .= $this->prepareNotifInvitation($n);
                }
            }
            
        $html .= '
                </ul>
            </div>';
            
        $notifsnew = '';
        if($notifsnum > 0)
            $notifsnew = 'class="red"';
            
        return $html;
    }

    function ajaxSubscribed($jid) {
        $p = new Subscribed;
        $p->setTo(echapJid($jid))
          ->request();
    }
    
    function ajaxRefuse($jid) {
        $jid = echapJid($jid);
        $p = new Unsubscribed;
        $p->setTo($jid)
          ->request();
        
        $notifs = Cache::c('activenotifs');
        unset($notifs[$jid]);
        
        Cache::c('activenotifs', $notifs);
        
        RPC::call('movim_fill', 'notifs', $this->prepareNotifs());

        RPC::commit();
    }

    function ajaxAddRoster($jid) {
        $jid = echapJid($jid);
        $r = new AddItem;
        $r->setTo($jid)
          ->setFrom($this->user->getLogin())
          ->request();
    }
    
    function ajaxSubscribe($jid) {
        $jid = echapJid($jid);
        $p = new Subscribe;
        $p->setTo($jid)
          ->request();      
          
        $notifs = Cache::c('activenotifs');

   	    unset($notifs[$jid]);
   	    
	    Cache::c('activenotifs', $notifs);
        
        RPC::call('movim_fill', 'notifs', $this->prepareNotifs());
        
        RPC::commit();
    }*/

    /*
     * Prepare a notification for incoming invitation
     * @return string
     */  
    /*function prepareNotifInvitation($from) {
        $html .= '
            <li>
                <form id="acceptcontact">
                    <p>' . $this->__('wants_to_talk', $from) . '</p>
                    <div class="clear spacetop"></div>
                    <a 
                        class="button color green merged left " 
                        id="notifsvalidate" 
                        onclick="
                            '.$this->call("ajaxAddRoster", "'".$from."'").'
                            setTimeout(function() {'.
                                $this->call("ajaxSubscribed", "'".$from."'").
                            '}, 1000);
                            setTimeout(function() {'.
                                $this->call("ajaxSubscribe", "'".$from."'").
                            '}, 2000);
                        ">
                        <i class="fa fa-plus"></i> '.t("Add").'
                    </a><a 
                        class="button color red alone merged right" 
                        onclick="'.$this->call("ajaxRefuse", "'".$from."'").'">
                        <i class="fa fa-times"></i> 
                    </a>
                </form>
                <div class="clear"></div>
            </li>';
            
        return $html;
    }*/
}
