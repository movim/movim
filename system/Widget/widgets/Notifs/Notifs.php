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
    
    function onMessage($payload) {
        global $sdb;
        $contact = new Contact();
        $sdb->load($contact, array('key' => $this->user->getLogin(), 'jid' => reset(explode("/", $payload['from']))));
        RPC::call('notification', $contact->getTrueName(), RPC::cdata($payload['movim']['body'], ENT_COMPAT, "UTF-8"));
        RPC::commit();
    }
    
    function onSubscribe($payload) {
   	    $notifs = Cache::c('activenotifs');
   	    
   	    $html = '
            <li>
                '.$payload['@attributes']['from'].' '.t('wants to talk with you'). ' <br />
   	            <input id="notifsalias" class="tiny" value="'.$payload['@attributes']['from'].'" onfocus="myFocus(this);" onblur="myBlur(this);"/>
   	            <a class="button tiny icon yes merged right" href="#" onclick="'.$this->genCallAjax("ajaxSubscribed", "'".$payload['@attributes']['from']."'").' showAlias(this);">'.t("Accept").'</a>
   	            <a class="button tiny icon add merged right" href="#" id="notifsvalidate" onclick="'.$this->genCallAjax("ajaxAccept", "'".$payload['@attributes']['from']."'", "getAlias()").' hideNotification(this);">'.t("Validate").'</a>
   	            <a class="button tiny icon yes merged left" href="#" onclick="'.$this->genCallAjax("ajaxRefuse", "'".$payload['@attributes']['from']."'").' hideNotification(this);">'.t("Decline").'</a>
   	        </li>';
   	    $notifs['sub'.$payload['@attributes']['from']] = $html;
   	    
        RPC::call('movim_prepend', 'notifslist', RPC::cdata($html));
        
	    Cache::c('activenotifs', $notifs);
    }
    
    function ajaxSubscribed($jid) {
        movim_log($jid);
        $this->xmpp->subscribedContact($jid);
    }
    
    function ajaxRefuse($jid) {
        $this->xmpp->unsubscribed($jid);
        
   	    $notifs = Cache::c('activenotifs');
   	    unset($notifs['sub'.$jid]);
   	    
	    Cache::c('activenotifs', $notifs);
    }
    
    function ajaxAccept($jid, $alias) {
        $this->xmpp->acceptContact($jid, false, $alias);
        
   	    $notifs = Cache::c('activenotifs');
   	    unset($notifs['sub'.$jid]);
   	    
	    Cache::c('activenotifs', $notifs);
    }
    
    function ajaxAddContact($jid, $alias) {
        $this->xmpp->addContact($jid, false, $alias);
    }
    
    function build() {  
    $notifs = Cache::c('activenotifs');
    if($notifs == false)
        $notifs = array();
    ?>
    <div id="notifs">
        <ul id="notifslist">
            <?php
            ksort($notifs);
            foreach($notifs as $key => $value) {
                    echo $value;
            }
            ?>
            <li>
                <input id="addjid" class="tiny" value="user@server.tld" onfocus="myFocus(this);" onblur="myBlur(this);"/>
                <input id="addalias" class="tiny" value="<?php echo t('Alias'); ?>" onfocus="myFocus(this);" onblur="myBlur(this);"/>
                <a class="button tiny icon yes" href="#" id="addvalidate" onclick="<?php $this->callAjax("ajaxAddContact", "getAddJid()", "getAddAlias()"); ?>"><?php echo t('Validate'); ?></a>
                <a class="button tiny icon add" href="#" onclick="addJid(this);"><?php echo t('Add a contact'); ?></a>
            </li>
        </ul>
    </div>
    <?php    
    }
}
