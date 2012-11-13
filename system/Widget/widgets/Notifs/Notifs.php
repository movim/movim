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
        $this->registerEvent('nonotification', 'onNoNotification');
    }
    
    function ajaxGetNotifications() {
        $p = new moxl\NotificationGet();
        $p->setTo($this->user->getLogin())
          ->request();
    }
    
    function onNotification($item) {
        $arr = explodeURI((string)$item->entry->link[0]->attributes()->href);
        $post = end(explode('/', $arr['node']));
        
   	    $notifs = Cache::c('activenotifs');
        
        $html = '';
        foreach($notifs as $key => $value)
            $html .= $this->prepareNotifs($key, $value);
    
        $explodedurn = explodeURI((string)$item->entry->link[0]->attributes()->href);
        $id = end(explode('/', $explodedurn['node']));
        
        $request = $this->genCallAjax(
                            "ajaxGetComments", 
                            "'".$this->user->getLogin()."'", 
                            "'".$id."'");

        $nhtml = '
        
            <li>
                <a href="?q=friend&f='.$arr['path'].'&p='.$post.'"
                   onclick="'.$request.'">
                    <span style="font-weight: bold;">'.
                        (string)$item->entry->source->author->name.'
                    </span> - '.prepareDate(strtotime((string)$item->entry->published)).'<br />'.
                    (string)$item->entry->content.'
                </a>
            </li>
                ';
        
        $notifs[(string)$item->attributes()->id] = $nhtml;
   	    
        RPC::call('movim_fill', 'notifs', RPC::cdata($html));
        
	    Cache::c('activenotifs', $notifs);
    }
    
    function onNoNotification() {
        RPC::call('movim_fill', 'notifs', RPC::cdata($this->prepareNotifs()));
    }
    
    function prepareNotifs()
    {
        $notifsnum = 0;
              
        $html .= '
            <div id="notifslist">
                <a 
                    class="button tiny icon follow black" 
                    href="#" 
                    style="margin: 5px;"
                    onclick="'.$this->genCallAjax("ajaxGetNotifications").';
                            this.innerHTML = \''.t('Updating').'\'; 
                            this.className= \'button tiny icon loading black\';
                            this.onclick=null;">
                    '.t('Rafraichir').'
                </a>
                <ul>';
            // XMPP notifications
            $notifs = Cache::c('activenotifs');

            if($notifs == false)
                $notifs = array();
            
            
            if(sizeof($notifs) != 0) {
                $notifsnum += sizeof($notifs);
                
                $html .= '
                <li class="title">'.
                    t('Notifications').'
                    <span class="num">'.sizeof($notifs).'</span>
                </li>';
                
                foreach($notifs as $n => $val) {
                    if($val == 'sub')
                        $html .= $this->prepareNotifInvitation($n);
                    else
                        $html .= $val;
                }
            
            }           
            
            // Contact request pending
            $query = RosterLink::query()->where(
                                        array(
                                            'key' => $this->user->getLogin(),
                                            'rosterask' => 'subscribe'));
            $subscribes = Contact::run_query($query);
            
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
                            <a href="?q=friend&f='.$s->getData('jid').'">
                            <img class="avatar"  src="'.Contact::getPhotoFromJid('xs', $s->getData('jid')).'" />
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
            
        $html = '
            <div id="notifstab" onclick="showNotifsList();">
                <span '.$notifsnew.'>'.
                    $notifsnum.'
                </span>
            </div>'.$html;
        
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
        
        RPC::call('movim_fill', 'notifs', RPC::cdata($this->prepareNotifs()));

        RPC::commit();
    }
    
    function ajaxAccept($jid, $alias) {  
        $r = new moxl\RosterAddItem();
        $r->setTo($jid)
          ->request();
        
        $p = new moxl\PresenceSubscribe();
        $p->setTo($jid)
          ->request();
          
        $p = new moxl\PresenceSubscribed();
        $p->setTo($jid)
          ->request();          
          
        $notifs = Cache::c('activenotifs');

   	    unset($notifs[$jid]);
   	    
	    Cache::c('activenotifs', $notifs);
        
        RPC::call('movim_fill', 'notifs', RPC::cdata($this->prepareNotifs()));
        
        RPC::commit();
    }
    
    function prepareNotifInvitation($from) {
        $html .= '
            <li>
                <form id="acceptcontact">
                    <p>'.$from.' '.t('wants to talk with you'). '</p>
                    <a 
                        class="button tiny icon add merged right black" 
                        href="#" id="notifsvalidate" 
                        onclick="'.$this->genCallAjax("ajaxAccept", "'".$from."'", "'alias'").'">'.
                        t("Add").'
                    </a><a 
                        class="button tiny icon no merged left black" 
                        href="#" 
                        onclick="'.$this->genCallAjax("ajaxRefuse", "'".$from."'").'">'.
                        t("Decline").'
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
