<?php

/**
 * @package Widgets
 *
 * @file Chat.php
 * This file is part of MOVIM.
 * 
 * @brief A jabber chat widget.
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

class Chat extends WidgetBase
{
	function WidgetLoad()
	{
    	$this->addcss('chat.css');
    	$this->addjs('chat.js');
		$this->registerEvent('message', 'onMessage');
		$this->registerEvent('composing', 'onComposing');
//		$this->registerEvent('paused', 'onPaused');
    }
    
    function onMessage($payload)
    {
        global $sdb;
        $contact = new Contact();
        $user = new User();
        $sdb->load($contact, array('key' => $user->getLogin(), 'jid' => reset(explode("/", $payload['from']))));
        if($contact->getData('chaton') != 1) {
            RPC::call('movim_prepend',
                           'chats',
                           RPC::cdata($this->prepareChat($contact)));
            $contact->chaton = 1;
            $sdb->save($contact);
        }
        
        RPC::call('movim_append',
                       'messages'.$contact->getData('jid'),
                       RPC::cdata('<div class="message"><span class="date">'.date('G:i', time()).'</span>'.prepareString(htmlentities($payload['movim']['body'], ENT_COMPAT, "UTF-8")).'</div>'));   
                       
        RPC::call('hideComposing',
                       $contact->getData('jid')); 
                       
        RPC::call('scrollTalk',
                       'messages'.$contact->getData('jid'));
                       
        RPC::call('newMessage');
            
    }
    
    function onComposing($payload)
    {
        global $sdb;
        $contact = new Contact();
        $user = new User();
        $sdb->load($contact, array('key' => $user->getLogin(), 'jid' => reset(explode("/", $payload['from']))));
        if($contact->getData('chaton') == 1) {
            RPC::call('showComposing',
                       $contact->getData('jid'));
                           
            RPC::call('scrollTalk',
                      'messages'.$contact->getData('jid'));
        }
    }
    
	/**
	 * Open a new talk
	 *
	 * @param string $jid
	 * @return void
	 */
	function ajaxOpenTalk($jid) 
	{
        global $sdb;
        $contact = new Contact();
        $user = new User();
        $sdb->load($contact, array('key' => $user->getLogin(), 'jid' => $jid));
        if($contact->getData('chaton') != 1) {
            RPC::call('movim_prepend',
                           'chats',
                           RPC::cdata($this->prepareChat($contact)));
            $contact->chaton = 1;
            $sdb->save($contact);
            RPC::commit();
        }
    }
    
	/**
     * Send a message
     *
     * @param string $to
     * @param string $message
     * @return void
     */
    function ajaxSendMessage($to, $message)
    {
		$xmpp = Jabber::getInstance();
		// We decode URL codes to send the correct message to the XMPP server
        $xmpp->sendMessage($to, rawurldecode($message));
    }
    
	/**
	 * Close a talk
	 *
	 * @param string $jid
	 * @return void
	 */
	function ajaxCloseTalk($jid) 
	{
        global $sdb;
        $contact = new Contact();
        $user = new User();
        $sdb->load($contact, array('key' => $user->getLogin(), 'jid' => $jid));
        if($contact->getData('chaton') == 1) {
            $contact->chaton = 0;
            $sdb->save($contact);
        }
	}
    
    function prepareChat($contact)
    {
        $html = '
            <div class="chat" onclick="this.querySelector(\'textarea\').focus()">'.
                '<div class="messages" id="messages'.$contact->getData('jid').'"><div style="display: none;" class="message" id="composing'.$contact->getData('jid').'">'.t('Composing...').'</div></div>'.
                '<textarea
                    onkeypress="if(event.keyCode == 13) {'.$this->genCallAjax('ajaxSendMessage', "'".$contact->getData('jid')."'", "sendMessage(this, '".$contact->getData('jid')."')").' return false;}"
                ></textarea>'.
                '<span>'.$contact->getTrueName().'</span>'.
                '<span class="cross" onclick="'.$this->genCallAjax("ajaxCloseTalk", "'".$contact->getData('jid')."'").' closeTalk(this)"></span>'.
            '</div>';
        return $html;
    }
    
    function build()
    {
        global $sdb;
        $user = new User();
        $contacts = $sdb->select('Contact', array('key' => $user->getLogin(), 'chaton' => 1));
        echo '<div id="chats">';
        if($contacts != false) {
            foreach($contacts as $contact) {
                echo $this->prepareChat($contact);
            }
        }
        echo '</div>';
    }
}
