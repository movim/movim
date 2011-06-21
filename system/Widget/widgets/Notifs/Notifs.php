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
   	    /*$notifs = Cache::c('activenotifs');
   	    $notifs = array();
	    Cache::c('activenotifs', $notifs);*/
    	//$this->addjs('friends.js');
		$this->registerEvent('incomesubscribe', 'onSubscribe');
    }
    
    function onSubscribe($payload) {
   	    $notifs = Cache::c('activenotifs');
   	    
   	    $html = '
            <li>
                '.$payload['from'].' '.t('wants to talk with you'). ' <br />
   	            <input id="notifsalias" class="tiny" value="'.$payload['from'].'" onfocus="myFocus(this);" onblur="myBlur(this);"/>
   	            <a href="#" onclick="showAlias(this);">'.t("Accept").'</a>
   	            <a href="#" id="notifsvalidate" onclick="'.$this->genCallAjax("ajaxAccept", "'".$payload['from']."'", "getAlias()").' hideNotification(this);">'.t("Validate").'</a>
   	            <a href="#" onclick="'.$this->genCallAjax("ajaxRefuse", "'".$payload['from']."'").' hideNotification(this);">'.t("Decline").'</a>
   	            <div class="clear"></div>
   	        </li>';
   	    $notifs['sub'.$payload['from']] = $html;
   	    
        RPC::call('movim_prepend', 'notifslist', RPC::cdata($html));
        
	    Cache::c('activenotifs', $notifs);
    }
    
    function ajaxRefuse($jid) {
		$xmpp = Jabber::getInstance();
        $xmpp->unsubscribed($jid);
        
   	    $notifs = Cache::c('activenotifs');
   	    unset($notifs['sub'.$jid]);
   	    
	    Cache::c('activenotifs', $notifs);
    }
    
    function ajaxAccept($jid, $alias) {
		$xmpp = Jabber::getInstance();
        $xmpp->addContact($jid, false, $alias);
        
   	    $notifs = Cache::c('activenotifs');
   	    unset($notifs['sub'.$jid]);
   	    
	    Cache::c('activenotifs', $notifs);
    }
    
    function build() {  
    $notifs = Cache::c('activenotifs');
    ?>
    <div id="notifs">
        <ul id="notifslist">
            <?php
            ksort($notifs);
            foreach($notifs as $key => $value) {
                    echo $value;
            }
            ?>
        </ul>
    </div>
    <?php    
    }
}
