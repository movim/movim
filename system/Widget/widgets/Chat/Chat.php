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
        
        $this->cached = false;
    }
    
    function onPresence($presence)
    {
	    $arr = $presence->getPresence();
	    $tab = PresenceHandler::getPresence($arr['jid'], true);

        $txt = array(
                1 => t('Online'),
                2 => t('Away'),
                3 => t('Do Not Disturb'),
                4 => t('Extended Away'),
                5 => t('Offline'),
            );
    
	    
        $html = '
            <div class="message presence">
                <span class="date">'.date('G:i', time()).'</span>'.
                prepareString(htmlentities($txt[$tab['presence']], ENT_COMPAT, "UTF-8")).'
            </div>';

        RPC::call('movim_append',
                       'messages'.$tab['jid'],
                       RPC::cdata($html)); 
                       
        RPC::call('scrollTalk',
                       'messages'.$tab['jid']);
	}
    
    function onMessage($message)
    {
        if($message->getData('key') == $message->getData('from'))
            $jid = $message->getData('to');
        else
            $jid = $message->getData('from');
    
        global $sdb;
        $contact = new Contact();
        $sdb->load($contact, array('key' => $this->user->getLogin(), 'jid' => $jid));
        
        if($contact->getData('chaton') == 0) {
            RPC::call('movim_prepend',
                           'chats',
                           RPC::cdata($this->prepareChat($contact)));
            RPC::call('scrollAllTalks');
            $contact->chaton = 1;
            $sdb->save($contact);
        }else if($message->getData('body') != '') {
            
            $html = $this->prepareMessage($message);

            if($contact->getData('chaton') == 2) {
                RPC::call('colorTalk',
                            'messages'.$contact->getData('jid'));
            }
            
            RPC::call('movim_append',
                           'messages'.$contact->getData('jid'),
                           RPC::cdata($html));
            
            RPC::call('hideComposing',
                           $contact->getData('jid')); 
                           
            RPC::call('scrollTalk',
                           'messages'.$contact->getData('jid'));
            //Sound and title notification               
            RPC::call('notify');
            //Highlight the new chat message
            RPC::call('setBackgroundColor', 'chatwindow'.$contact->getData('jid'), 'red');

        }            
        
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
        $item = array('@attributes' => array(
                                        'to' => $to,
                                        'from' => $this->user->getLogin()),
                      'body' => rawurldecode($message));
                      
        
        global $sdb;
        $m = new Message();
        $m->setMessageChat($item);
        $sdb->save($m);

        $this->onMessage($m);
             
		// We decode URL codes to send the correct message to the XMPP server
        $this->xmpp->sendMessage($to, rawurldecode($message));
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
        if($contact->getData('chaton') == 1 || $contact->getData('chaton') == 2) {
            $contact->chaton = 0;
            $sdb->save($contact);
        }
	}
    
    function ajaxHideTalk($jid)
    {
        global $sdb;
        $contact = new Contact();
        $sdb->load($contact, array('key' => $this->user->getLogin(), 'jid' => $jid));
        if($contact->getData('chaton') == 1) {
            $contact->chaton = 2;
            $sdb->save($contact);
        }
        else {
            $contact->chaton = 1;
            $sdb->save($contact);
        }
        
        RPC::call('scrollTalk',
                   'messages'.$contact->getData('jid'));
        RPC::commit();
    }
    
    function prepareMessage($message) {
        $html = '<div class="message ';
            if($message->getData('key') == $message->getData('from'))
                $html.= 'me';
               
        $content = $message->getData('body');
                
        if(preg_match("#^/me#", $message->getData('body'))) {
            $html .= "own ";
            $content = "** ".substr($message->getData('body'), 4);
        }
                
        $html .= '"><span class="date">'.date('H:i', strtotime($message->getData('published'))).'</span>';
        $html.= prepareString(htmlentities($content, ENT_COMPAT, "UTF-8")).'</div>';
        return $html;
    }
    
    function prepareChat($contact)
    {
        $query = Message::query()
                  ->where(
                        array(
                            'key' => $this->user->getLogin(), 
                                array('to' => $contact->getData('jid') , '|from' => $contact->getData('jid')) 
                        )
                    )
                  ->orderby('published', true)
                  ->limit(0, 20);
        $messages = Message::run_query($query);

        if(!empty($messages)) {
            $messages = array_reverse($messages);
            $day = '';
            foreach($messages as $m) {
                if($day != date('d',strtotime($m->getData('published')))) {
                    $messageshtml .= '<div class="message presence">'.prepareDate(strtotime($m->getData('published')), false).'</div>';
                    $day = date('d',strtotime($m->getData('published')));
                }
                $messageshtml .= $this->prepareMessage($m);
            }
        }
        
        $style = '';
        if($contact->getData('chaton') == 2) {
            $style = ' style="display: none;" ';
        }
    
        $html = '
            <div class="chat" onclick="this.querySelector(\'textarea\').focus()" id="chatwindow'.$contact->getData('jid').'">'.
                '<div class="messages" '.$style.' id="messages'.$contact->getData('jid').'">'.$messageshtml.'<div style="display: none;" class="message" id="composing'.$contact->getData('jid').'">'.t('Composing...').'</div></div>'.
                '<textarea onkeyup="movim_textarea_autoheight(this);"  '.$style.'
                    onkeypress="if(event.keyCode == 13) {'.$this->genCallAjax('ajaxSendMessage', "'".$contact->getData('jid')."'", "sendMessage(this, '".$contact->getData('jid')."')").' return false; }"
					onfocus="setBackgroundColor(\'chatwindow'.$contact->getData('jid').'\', \'#444444\')"
                ></textarea>'.
                '<a class="name" onclick="'.$this->genCallAjax("ajaxHideTalk", "'".$contact->getData('jid')."'").' hideTalk(this);">'.
                    '<img class="avatar"  src="'.$contact->getPhoto('xs').'" /><span>'.$contact->getTrueName().'</span>'.
                '</a>'.
                '<span class="cross" onclick="'.$this->genCallAjax("ajaxCloseTalk", "'".$contact->getData('jid')."'").' closeTalk(this)"></span>'.
            '</div>';
        return $html;
    }
    
    function build()
    {
        $query = Contact::query()
                          ->where(
                                array(
                                    'key' => $this->user->getLogin(), 
                                    array(
                                        'chaton' => 
                                        array(1, '|2'))
                                )
                            );
        $contacts = Contact::run_query($query);
        
        echo '<div id="chats">';
        if($contacts != false) {
            foreach($contacts as $contact) {
                echo $this->prepareChat($contact);
            }
        }
        echo '</div>';
    }
}
