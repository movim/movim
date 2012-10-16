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

		$this->registerEvent('message', 'onMessage');
		$this->registerEvent('subscribe', 'onSubscribe');
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
    
    function prepareNotifs($from) {
        $html = '';
        
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
            
        return $html;
    }
    
    function onSubscribe($from) {
   	    $notifs = Cache::c('activenotifs');
        
        $html = '';
        foreach($notifs as $key => $value)
            $html .= $this->prepareNotifs($key);
   	    //$notifs['sub'.$from] = $html;
   	    
        RPC::call('movim_fill', 'notifslist', RPC::cdata($html));
        
	    Cache::c('activenotifs', $notifs);
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
    
    function build() {  
    $notifs = Cache::c('activenotifs');
    if($notifs == false)
        $notifs = array();
        
    /*$query = RosterLink::query()
                     ->where(
                        array(
                            'key' => $this->user->getLogin(),
                            'jid!' => $this->user->getLogin(),
                            array(
                                'rostersubscription!' => 'none',
                                'rostersubscription!' => 'vcard',
                                '|rosterask' => 'subscribe')));
    $contacts = RosterLink::run_query($query);*/
    ?>
    <div id="notifs">
        <span id="widgettitle">
            <?php //echo t('Contacts (%s)', sizeof($contacts)); ?>
            <!--<a 
                    class="" 
                    href="#" 
                    id="addstart"
                    onclick="addJid(this);">
                    <?php echo t('Add'); ?>
            </a>-->
        </span>
        <ul id="notifslist">
            <?php
            ksort($notifs);
            foreach($notifs as $key => $value) {
                    echo $this->prepareNotifs($key);
            }
            ?>
        </ul>
    </div>
    <?php    
    }
}
