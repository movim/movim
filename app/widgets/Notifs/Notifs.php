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

class Notifs extends WidgetCommon
{
    function WidgetLoad()
    {
    	$this->addcss('notifs.css');
    	$this->addjs('notifs.js');
        $this->registerEvent('notification', 'onNotification');
        $this->registerEvent('notificationdelete', 'onNotificationDelete');
        $this->registerEvent('notifications', 'displayNotifications');
        $this->registerEvent('nonotification', 'onNoNotification');
    }
    
    /*
     * Retrieve all the notifications
     */  
    /*function ajaxGetNotifications() {
        $p = new moxl\NotificationGet();
        $p->setTo($this->user->getLogin())
          ->request();
    }*/
    
    /*
     * Delete a notification item
     */
    /*function ajaxDeleteNotification($id) {
        $d = new moxl\NotificationItemDelete();
        $d->setTo($this->user->getLogin())
          ->setId($id)
          ->request();
    }*/
    
    /*
     * Create the notification element from the inbox item
     * @param SimpleXMLElement $item
     */  
    /*function onNotification($item) {
        $arr = explodeURI((string)$item->entry->link[0]->attributes()->href);
        $post = end(explode('/', $arr['node']));
        
   	    $notifs = Cache::c('activenotifs');
        
        $request = $this->genCallAjax(
                            "ajaxGetComments", 
                            "'".$this->user->getLogin()."'", 
                            "'".$post."'");
                            
        $delete = $this->genCallAjax(
                            "ajaxDeleteNotification", 
                            "'".(string)$item->attributes()->id."'");

        $nhtml = '
        
            <li>
                <a href="'.Route::urlize('friend',$arr['path']).'"
                   onclick="'.$request.'">
                   <p>
                    <span style="font-weight: bold;">'.
                        (string)$item->entry->source->author->name.'
                    </span>'.prepareDate(strtotime((string)$item->entry->published)).'<br />'.
                    (string)$item->entry->content.'
                    </p>
                </a>

                <a class="delete" href="#" onclick="'.$delete.'">'.t('Delete').'</a>
                <div class="clear"></div>
            </li>
                ';
        
        $notifs[(string)$item->attributes()->id] = $nhtml;

	    Cache::c('activenotifs', $notifs);
    }*/
    
    /*
     * In notification deletion
     * @param string $id
     */ 
    /*function onNotificationDelete($id) {
        $notifs = Cache::c('activenotifs');
        unset($notifs[$id]);
	    Cache::c('activenotifs', $notifs);
        
        RPC::call('movim_fill', 'notifs', $this->prepareNotifs());
    }*/

    /*
     * Display all the notifications to the browser
     */    
    /*function displayNotifications() {
        RPC::call('movim_fill', 'notifs', $this->prepareNotifs());
    }*/
    
    /*
     * Display all the notifications to the browser if there is no new
     * notifications
     */  
    /*function onNoNotification() {
        RPC::call('movim_fill', 'notifs', $this->prepareNotifs());
    }*/
    
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
            $cd = new \modl\ContactDAO();
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
            
            
        $html .= '
                </ul>
            </div>';
            
        $notifsnew = '';
        if($notifsnum > 0)
            $notifsnew = 'class="red"';
            
        /*$html = '
            <div id="notifstab" onclick="showNotifsList();">
                <span '.$notifsnew.'>'.
                    $notifsnum.'
                </span>
            </div>'.$html;
        */
        return $html;
    }
    
    function ajaxSubscribed($jid) {
        $p = new moxl\PresenceSubscribed();
        $p->setTo($jid)
          ->request();
    }
    
    function ajaxRefuse($jid) {
        $p = new moxl\PresenceUnsubscribed();
        $p->setTo($jid)
          ->request();
        
        $notifs = Cache::c('activenotifs');
        unset($notifs[$jid]);
        
        Cache::c('activenotifs', $notifs);
        
        RPC::call('movim_fill', 'notifs', $this->prepareNotifs());

        RPC::commit();
    }

    function ajaxAddRoster($jid) {
        $r = new moxl\RosterAddItem();
        $r->setTo($jid)
          ->setFrom($this->user->getLogin())
          ->request();
    }
    
    function ajaxSubscribe($jid) { 
        $p = new moxl\PresenceSubscribe();
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
    
    function build()
    {
        ?>
        <div id="notifs">
            <?php echo $this->prepareNotifs(); ?>
        </div>
        <?php
    }
}
