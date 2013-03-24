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
        $this->registerEvent('paused', 'onPaused');
		$this->registerEvent('presence', 'onPresence');
        
        $this->cached = false;
    }
    
    function onPresence($presence)
    {
	    $arr = $presence->getPresence();

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
                prepareString(htmlentities($txt[$arr['presence']], ENT_COMPAT, "UTF-8")).'
            </div>';

        RPC::call('movim_append',
                       'messages'.$arr['jid'],
                       RPC::cdata($html)); 
                       
        RPC::call('scrollTalk',
                       'messages'.$arr['jid']);
	}
    
    function onMessage($message)
    {
        if($message->key == $message->from) {
            $key = $message->from;
            $jid = $message->to;
        } else {
            $key = $message->to;
            $jid = $message->from;
        }

        $rd = new \modl\RosterLinkDAO();
        
        $rc = new \modl\ContactDAO();
        $contact = $rc->getRosterItem(echapJid($jid));
        
        if(isset($contact) && $contact->chaton == 0) {
            $contact->chaton = 2;
            $rd->setChat($jid, 2);
            
            RPC::call('movim_prepend',
                           'chats',
                           RPC::cdata($this->prepareChat($contact)));
            RPC::call('scrollAllTalks');
        } else if(isset($contact) && $message->body != '') {
            
            $html = $this->prepareMessage($message);

            if($contact->chaton == 1) {
                RPC::call('colorTalk',
                            'messages'.$contact->jid);
            }
            
            RPC::call('movim_append',
                           'messages'.$contact->jid,
                           RPC::cdata($html));
            
            RPC::call('hideComposing',
                           $contact->jid); 

            RPC::call('hidePaused',
                           $contact->jid); 
                           
            RPC::call('scrollTalk',
                           'messages'.$contact->jid);
            //Sound and title notification               
            RPC::call('notify');
            //Highlight the new chat message
            RPC::call('setBackgroundColor', 'chatwindow'.$contact->jid, 'red');

        }            
        
        RPC::commit();

    }
    
    function onComposing($jid)
    {       
        $rd = new \modl\RosterLinkDAO();
        $contact = $rd->get(echapJid($jid));
        
        if(in_array($contact->chaton, array(1, 2))) {
            RPC::call('showComposing',
                       $contact->jid);
                           
            RPC::call('scrollTalk',
                      'messages'.$contact->jid);
        }
    }

    function onPaused($jid)
    {        
        $rd = new \modl\RosterLinkDAO();
        $contact = $rd->get(echapJid($jid));
        
        if(in_array($contact->chaton, array(1, 2))) {
            RPC::call('showPaused',
                       $contact->jid);
                           
            RPC::call('scrollTalk',
                      'messages'.$contact->jid);
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
        $rc = new \modl\ContactDAO();
        $contact = $rc->getRosterItem(echapJid($jid));

        if(
            isset($contact) 
         && $contact->chaton == 0 
         && !in_array($contact->presence, array(5, 6))) {
             
            $contact->chaton = 2;
            
            $rd = new \modl\RosterLinkDAO();
            $rd->setChat($jid, 2);
            
            RPC::call('movim_prepend',
                           'chats',
                           RPC::cdata($this->prepareChat($contact)));
            RPC::call('scrollAllTalks');

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
        $m = new \modl\Message();
        
        $m->key     = $this->user->getLogin();
        $m->to      = echapJid($to);
        $m->from    = $this->user->getLogin();
        
        $m->type    = 'chat';
        
        $m->body    = rawurldecode($message);
        
        $m->published = date('Y-m-d H:i:s');
        $m->delivered = date('Y-m-d H:i:s');
    
        $md = new \modl\MessageDAO();
        $md->set($m);

        $this->onMessage($m);
             
		// We decode URL codes to send the correct message to the XMPP server
        moxl\message($to, htmlspecialchars(rawurldecode($message)));
    }
    
	/**
	 * Close a talk
	 *
	 * @param string $jid
	 * @return void
	 */
	function ajaxCloseTalk($jid) 
	{                
        $rd = new \modl\RosterLinkDAO();
        $contacts = $rd->getChats();

        foreach($contacts as $contact) {
            if((int)$contact->chaton == 1 || (int)$contact->chaton == 2) {
                $contact->chaton = 0;
                $rd->setNow($contact);
            }
        }
	}
    
    function ajaxHideTalk($jid)
    {
        $rd = new \modl\RosterLinkDAO();
        $contact = $rd->get(echapJid($jid));
        
        if($contact->chaton == 1)
            $contact->chaton = 2;
        else 
            $contact->chaton = 1;
        $rd->setNow($contact);
        
        RPC::call('scrollTalk',
                   'messages'.$contact->jid);
        RPC::commit();
    }
    
    function prepareMessage($message) {
        if($message->body != '') {
            $html = '<div class="message ';
                if($message->key == $message->from)
                    $html.= 'me';
                   
            $content = $message->body;
                    
            if(preg_match("#^/me#", $message->body)) {
                $html .= " own ";
                $content = "** ".substr($message->body, 4);
            }
                    
            $html .= '"><span class="date">'.date('H:i', strtotime($message->published)).'</span>';
            $html.= prepareString(htmlentities($content, ENT_COMPAT, "UTF-8")).'</div>';
            return $html;
        } else {
            return '';
        }
    }
    
    function prepareChat($contact)
    {
        $md = new \modl\MessageDAO();
        $messages = $md->getContact($contact->jid);

        if(!empty($messages)) {
            $day = '';
            foreach($messages as $m) {
                if($day != date('d',strtotime($m->published))) {
                    $messageshtml .= '<div class="message presence">'.prepareDate(strtotime($m->published), false).'</div>';
                    $day = date('d',strtotime($m->published));
                }
                $messageshtml .= $this->prepareMessage($m);
            }
        }
        
        $style = '';
        if($contact->chaton == 2) {
            $tabstyle = ' style="display: none;" ';            
            $panelstyle = ' style="display: block;" ';
        }
        
        $html = '
            <div class="chat" onclick="this.querySelector(\'textarea\').focus()">
                <div class="panel" '.$panelstyle.'>
                    <div class="head" >
                        <span class="chatbutton cross" onclick="'.$this->genCallAjax("ajaxCloseTalk", "'".$contact->jid."'").' closeTalk(this)"></span>
                        <span class="chatbutton arrow" onclick="'.$this->genCallAjax("ajaxHideTalk", "'".$contact->jid."'").' hideTalk(this)"></span>
                        <img class="avatar"  src="'.$contact->getPhoto('xs').'" />
                        <a class="name" href="?q=friend&f='.$contact->jid.'">
                            '.$contact->getTrueName().'
                        </a>
                        <div class="clear"></div>
                    </div>
                    <div class="messages" id="messages'.$contact->jid.'">
                        '.$messageshtml.'
                        <div style="display: none;" class="message" id="composing'.$contact->jid.'">'.t('Composing...').'</div>
                        <div style="display: none;" class="message" id="paused'.$contact->jid.'">'.t('Paused...').'</div>
                    </div>
                    
                    <div class="text">
                         <textarea 
                            rows="1"
                            onkeyup="movim_textarea_autoheight(this);"
                            onkeypress="if(event.keyCode == 13) {'.$this->genCallAjax('ajaxSendMessage', "'".$contact->jid."'", "sendMessage(this, '".$contact->jid."')").' return false; }"
                            onfocus="setBackgroundColor(\'chatwindow'.$contact->jid.'\', \'#444444\')"
                        ></textarea>
                    </div>
                </div>
                
                <div class="tab '.$tabclass.'" '.$tabstyle.' onclick="'.$this->genCallAjax("ajaxHideTalk", "'".$contact->jid."'").' showTalk(this);">
                    <div class="name">
                        <img class="avatar"  src="'.$contact->getPhoto('xs').'" />'.$contact->getTrueName().'
                    </div>
                </div>
            </div>
            ';
        return $html;
    }
    
    function build()
    {        
        $rc = new \modl\ContactDAO();
        $contacts = $rc->getRosterChat();
        
        echo '<div id="chats">';
        if(isset($contacts)) {
            foreach($contacts as $contact) {
                echo $this->prepareChat($contact);
            }
        }
        echo '</div>';
    }
}
