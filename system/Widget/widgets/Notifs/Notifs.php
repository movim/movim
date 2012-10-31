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

class Notifs extends WidgetBase
{
    function WidgetLoad()
    {
    	$this->addcss('notifs.css');
    	$this->addjs('notifs.js');
        $this->registerEvent('notification', 'onNotification');
    }
    
    function ajaxGetNotifications() {
        $p = new moxl\NotificationGet();
        $p->setTo($this->user->getLogin())
          ->request();
    }
    
    function prepareNotifs()
    {
        $notifsnum = 0;
              
        $html .= '
            <div id="notifslist">
                <a 
                    class="button tiny icon follow" 
                    href="#" 
                    id="refresh"
                    onclick="'.$this->genCallAjax("ajaxGetNotifications").';
                            this.innerHTML = \''.t('Updating').'\'; 
                            this.className= \'button tiny icon merged right loading\';
                            this.onclick = \'return false;\';">
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
    
    function onNotification($item) {
        $arr = explodeURI((string)$item->entry->link[0]->attributes()->href);
        $post = end(explode('/', $arr['node']));
        //movim_log(explodeURI($item->entry->link[0]->attributes()->href));
   	    $notifs = Cache::c('activenotifs');
        
        $html = '';
        foreach($notifs as $key => $value)
            $html .= $this->prepareNotifs($key, $value);
        
        $nhtml = '
        
            <li>
                <a href="?q=friend&f='.$arr['path'].'&p='.$post.'">
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
    
    function build()
    {
        ?>
        <div id="notifs">
            <?php echo $this->prepareNotifs(); ?>
        </div>
        <?php
    }
}

/*
class Notifs extends WidgetBase
{
    function WidgetLoad()
    {
    	$this->addcss('notifs.css');
    	$this->addjs('notifs.js');

		$this->registerEvent('message', 'onMessage');
		$this->registerEvent('subscribe', 'onSubscribe');
		$this->registerEvent('notification', 'onNotification');
    }
    
    function onMessage($message) {
        $query = Contact::query()->select()
                                 ->where(array(
                                            'jid' => $message->getData('from')));
        $contact = Contact::run_query($query);

        $contact = $contact[0];

        if(is_object($contact)) {
            RPC::call('notification', $contact->getTrueName(), RPC::cdata($message->getData('body'), ENT_COMPAT, "UTF-8"));
            RPC::commit();
        }
    }
    
    function prepareNotifs($from, $value = false) {
        $html = '';
        
        if($value == 'sub') {
            $html .= '
                <li>
                    <form id="acceptcontact">
                        '.$from.' '.t('wants to talk with you'). ' <br />
                        <div class="element large">
                            <label id="labelnotifsalias" for="notifsalias">'.t('Alias').'</label>
                            <input 
                                id="notifsalias"
                                class="tiny" 
                                value="'.$from.'" 
                                onfocus="myFocus(this);" 
                                onblur="myBlur(this);"
                            />
                        </div>
                        <a 
                            class="button tiny icon yes merged right" 
                            href="#" 
                            onclick="'.$this->genCallAjax("ajaxSubscribed", "'".$from."'").' showAlias(this);">'.
                            t("Accept").'
                        </a>
                        <a 
                            class="button tiny icon add merged right" 
                            href="#" id="notifsvalidate" 
                            onclick="'.$this->genCallAjax("ajaxAccept", "'".$from."'", "getAlias()").' hideNotification(this);">'.
                            t("Add").'
                        </a>
                        <a 
                            class="button tiny icon no merged left" 
                            href="#" 
                            onclick="'.$this->genCallAjax("ajaxRefuse", "'".$from."'").' hideNotification(this);">'.
                            t("Decline").'
                        </a>
                    </form>
                </li>';
        } elseif($value != false) {
            //$html .= $value;
        }
            
        return $html;
    }
    
    function onSubscribe($from) {
   	    $notifs = Cache::c('activenotifs');
        
        $html = '';
        foreach($notifs as $key => $value)
            $html .= $this->prepareNotifs($key, $value);
   	    
        RPC::call('movim_fill', 'notifslist', RPC::cdata($html));
        
	    Cache::c('activenotifs', $notifs);
    }*/

    /*
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
    }
    
    function ajaxAccept($jid, $alias) {        
        $r = new moxl\RosterAddItem();
        $r->setTo($jid)
          ->request();
        
        $p = new moxl\PresenceSubscribe();
        $p->setTo($jid)
          ->request();
        
   	    $notifs = Cache::c('activenotifs');
   	    unset($notifs[$jid]);
   	    
	    Cache::c('activenotifs', $notifs);
    }
    
    function ajaxGetNotifications() {
        $p = new moxl\NotificationGet();
        $p->setTo($this->user->getLogin())
          ->request();
    }
    
    function build() {  
    $notifs = Cache::c('activenotifs');
    if($notifs == false)
        $notifs = array();
        
    ?>
    <div id="notifs">
        <!--<a 
            class="button tiny icon yes" 
            href="#" 
            id="addvalidate" 
            onclick="<?php $this->callAjax("ajaxGetNotifications"); ?>">
            <?php echo t('Send request'); ?>
        </a>-->
        <ul id="notifslist">
            <?php
            ksort($notifs);
            foreach($notifs as $key => $value) {
                    echo $this->prepareNotifs($key, $value);
            }
            ?>
        </ul>
    </div>
    <?php    
    }
}*/
