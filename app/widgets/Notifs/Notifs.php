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
use Moxl\Xec\Action\Presence\Unsuscribed;
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
        $notifsnum = 0;
              
        $html = '
            <div id="notifslist">
                <!--<a 
                    class="button icon color green refresh" 
                    style="margin: 0.5em;"
                    onclick="'.$this->genCallAjax("ajaxGetNotifications").';
                            this.innerHTML = \''.t('Updating').'\'; 
                            this.className= \'button color orange icon loading\';
                            this.onclick=null;">
                    '.t('Refresh').'
                </a>-->
                <ul>';
            // XMPP notifications
            $notifs = Cache::c('activenotifs');

            if($notifs == false)
                $notifs = array();
            
            
            if(sizeof($notifs) != 0) {
                $notifsnum += sizeof($notifs);
                
                /*$html .= '
                <li class="title">'.
                    t('Notifications').'
                    <span class="num">'.sizeof($notifs).'</span>
                </li>';*/
                
                foreach($notifs as $n => $val) {
                    if($val == 'sub')
                        $html .= $this->prepareNotifInvitation($n);
                    //else
                    //    $html .= $val;
                }
            
            }           
            
            // Contact request pending
            /*$cd = new \modl\ContactDAO();
            $subscribes = $cd->getRosterSubscribe();
            
            if(sizeof($subscribes) != 0) {
                $notifsnum += sizeof($subscribes);
                
                $html .= '
                <li class="title">'.
                    t('Contact request pending').'
                    <span class="num">'.sizeof($subscribes).'</span>
                </li>';
                
                foreach($subscribes as $s) {
                    $html .= '
                        <li>
                            <a href="'.Route::urlize('friend', $s->jid).'">
                            <img class="avatar" src="'.$s->getPhoto('s').'" />
                            '.
                                $s->getTrueName().'
                            </a>
                        </li>';
                }
            
            }
            */
            
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
    }

    /*
     * Prepare a notification for incoming invitation
     * @return string
     */  
    function prepareNotifInvitation($from) {
        $html .= '
            <li>
                <form id="acceptcontact">
                    <p>'.$from.' '.t('wants to talk with you'). '</p>
           
                        <a 
                            class="button color green icon add merged left " 
                            id="notifsvalidate" 
                            onclick="
                                '.$this->genCallAjax("ajaxAddRoster", "'".$from."'").'
                                setTimeout(function() {'.
                                    $this->genCallAjax("ajaxSubscribed", "'".$from."'").
                                '}, 1000);
                                setTimeout(function() {'.
                                    $this->genCallAjax("ajaxSubscribe", "'".$from."'").
                                '}, 2000);
                            ">'.
                            t("Add").'
                        </a><a 
                            class="button color red alone icon no merged right" 
                            onclick="'.$this->genCallAjax("ajaxRefuse", "'".$from."'").'">
                        </a>
       
                </form>
                <div class="clear"></div>
            </li>';
            
        return $html;
    }
}
