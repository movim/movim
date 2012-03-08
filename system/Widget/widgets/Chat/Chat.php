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
		$this->registerEvent('presence', 'onPresence');
    }
    
    function cacheMessage($jid, $html) {
        if(Cache::c('log'.$jid) == false)
            Cache::c('log'.$jid, array());
        
        $log = Cache::c('log'.$jid);
        array_push($log, $html);
        if(count($log)>25) {
            array_shift($log);
        }
        Cache::c('log'.$jid, $log);
    }
    
    function onPresence($presence)
    {
	    $arr = $presence->getPresence();
	    $tab = PresenceHandler::getPresence($arr['jid'], true);

        $txt = array(
                1 => t('Online'),
                2 => t('Away'),
                3 => t('Do Not Disturb'),
                4 => t('Long Absence'),
                5 => t('Offline'),
            );
    
	    
        $html = '<div class="message presence"><span class="date">'.date('G:i', time()).'</span>'.prepareString(htmlentities($txt[$tab['presence']], ENT_COMPAT, "UTF-8")).'</div>';
        $this->cacheMessage($arr['jid'], $html);

        RPC::call('movim_append',
                       'messages'.$tab['jid'],
                       RPC::cdata($html)); 
                       
        RPC::call('scrollTalk',
                       'messages'.$tab['jid']);
	}
    
    function onMessage($payload)
    {
        $jid = reset(explode("/", $payload['from']));
    
        global $sdb;

        $contact = new Contact();
        $sdb->load($contact, array('key' => $this->user->getLogin(), 'jid' => $jid));
        
        if($contact->getData('chaton') != 1) {
            RPC::call('movim_prepend',
                           'chats',
                           RPC::cdata($this->prepareChat($contact)));
            RPC::call('scrollAllTalks');
            $contact->chaton = 1;
            $sdb->save($contact);
        }
        
        $html = '<div class="message ';
        
        $message = $payload['movim']['body'];
        
        if(preg_match("#^/me#", $message)) {
			$html .= "own ";
			$message = "** ".$contact->getTrueName()." ".substr($message, 4);
		}
		
		if($payload['me'] == true)
			$html .= "me";
		        
        $html .= '"><span class="date">'.date('G:i', time()).'</span>'.prepareString(htmlentities($message, ENT_COMPAT, "UTF-8")).'</div>';
        
        $this->cacheMessage($jid, $html);
        
        RPC::call('movim_append',
                       'messages'.$contact->getData('jid'),
                       RPC::cdata($html));   
                       
        RPC::call('hideComposing',
                       $contact->getData('jid')); 
                       
        RPC::call('scrollTalk',
                       'messages'.$contact->getData('jid'));
                       
        RPC::call('newMessage');
            
        RPC::commit();
    }
    
    function onComposing($payload)
    {
        global $sdb;
        $contact = new Contact();
        $sdb->load($contact, array('key' => $this->user->getLogin(), 'jid' => reset(explode("/", $payload['from']))));
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
        
        $presence = PresenceHandler::getPresence($jid, true);
        if(isset($presence) && $presence["presence_txt"] != 'offline') {	
			$contact = new Contact();
			$sdb->load($contact, array('key' => $this->user->getLogin(), 'jid' => $jid));
			if($contact->getData('chaton') != 1) {
				RPC::call('movim_prepend',
							   'chats',
							   RPC::cdata($this->prepareChat($contact)));
				RPC::call('scrollAllTalks');
				$contact->chaton = 1;
				$sdb->save($contact);
				RPC::commit();
			}
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
		// We decode URL codes to send the correct message to the XMPP server
        $this->xmpp->sendMessage($to, rawurldecode($message));
		
		$arr['from'] = $to;
		$arr['me'] = true;
		$arr['movim']['body'] = rawurldecode($message);
		$this->onMessage($arr);
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
        $sdb->load($contact, array('key' => $this->user->getLogin(), 'jid' => $jid));
        if($contact->getData('chaton') == 1) {
            $contact->chaton = 0;
            $sdb->save($contact);
        }
	}
    
    function prepareChat($contact)
    {
        $log = Cache::c('log'.$contact->getData('jid'));
        if(is_array($log)) {
            foreach($log as $key => $value)
                $m .= $value;
        }
    
        $html = '
            <div class="chat" onclick="this.querySelector(\'textarea\').focus()">'.
                '<div class="messages" id="messages'.$contact->getData('jid').'">'.$m.'<div style="display: none;" class="message" id="composing'.$contact->getData('jid').'">'.t('Composing...').'</div></div>'.
                '<textarea
                    onkeypress="if(event.keyCode == 13) {'.$this->genCallAjax('ajaxSendMessage', "'".$contact->getData('jid')."'", "sendMessage(this, '".$contact->getData('jid')."')").' return false;}"
                ></textarea>'.
                '<img class="avatar"  src="'.$contact->getPhoto('xs').'" /><span>'.$contact->getTrueName().'</span>'.
                '<span class="cross" onclick="'.$this->genCallAjax("ajaxCloseTalk", "'".$contact->getData('jid')."'").' closeTalk(this)"></span>'.
            '</div>';
        return $html;
    }
    
    function build()
    {

        global $sdb;
        $contacts = $sdb->select('Contact', array('key' => $this->user->getLogin(), 'chaton' => 1));
        echo '<div id="chats">';
        if($contacts != false) {
            foreach($contacts as $contact) {
                echo $this->prepareChat($contact);
            }
        }
        echo '</div>';
    }
}
