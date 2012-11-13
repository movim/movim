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
        if($message->getData('key') == $message->getData('from')) {
            $key = $message->getData('from');
            $jid = $message->getData('to');
        } else {
            $key = $message->getData('to');
            $jid = $message->getData('from');
        }

        $query = RosterLink::query()->select()
                                 ->where(array(
                                            'key' => $key,
                                            'jid' => $jid));
        $contact = RosterLink::run_query($query);

        if(isset($contact))
            $contact = $contact[0];
        
        if(isset($contact) && $contact->getData('chaton') == 0) {
            $contact->chaton->setval(2);
            $contact->run_query($contact->query()->save($contact));
            
            RPC::call('movim_prepend',
                           'chats',
                           RPC::cdata($this->prepareChat($contact)));
            RPC::call('scrollAllTalks');
        } else if(isset($contact) && $message->getData('body') != '') {
            
            $html = $this->prepareMessage($message);

            if($contact->getData('chaton') == 1) {
                RPC::call('colorTalk',
                            'messages'.$contact->getData('jid'));
            }
            
            RPC::call('movim_append',
                           'messages'.$contact->getData('jid'),
                           RPC::cdata($html));
            
            RPC::call('hideComposing',
                           $contact->getData('jid')); 

            RPC::call('hidePaused',
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
    
    function onComposing($jid)
    {
        $query = RosterLink::query()->select()
                                 ->where(array(
                                            'key' => $this->user->getLogin(),
                                            'jid' => echapJid($jid)));
        $contact = RosterLink::run_query($query);
        $contact = $contact[0];
        
        if(in_array($contact->getData('chaton'), array(1, 2))) {
            RPC::call('showComposing',
                       $contact->getData('jid'));
                           
            RPC::call('scrollTalk',
                      'messages'.$contact->getData('jid'));
        }
    }

    function onPaused($jid)
    {
        $query = RosterLink::query()->select()
                                 ->where(array(
                                            'key' => $this->user->getLogin(),
                                            'jid' => echapJid($jid)));
        $contact = RosterLink::run_query($query);
        $contact = $contact[0];
        
        if(in_array($contact->getData('chaton'), array(1, 2))) {
            RPC::call('showPaused',
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
        $query = RosterLink::query()->select()
                                 ->where(array(
                                            'RosterLink`.`key' => $this->user->getLogin(),
                                            'RosterLink`.`jid' => echapJid($jid)));
        $contact = RosterLink::run_query($query);
        $contact = $contact[0];

        $query = Presence::query()->select()
                                  ->where(array(
                                            'key' => $this->user->getLogin(),
                                            'jid' => echapJid($jid)))
                                  ->orderby('presence', false);
        $presence = Presence::run_query($query);
        $presence = $presence[0];

        if(isset($contact) && $contact->getData('chaton') == 0 && isset($presence) && !in_array($presence->presence->getval(), array(5, 6))) {
            $contact->chaton->setval(2);
            
            $contact->run_query($contact->query()->save($contact));
            
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
        $m = new \Message();
        
        $m->key->setval($this->user->getLogin());
        $m->to->setval(echapJid($to));
        $m->from->setval($this->user->getLogin());
        
        $m->type->setval("chat");
        
        $m->body->setval(rawurldecode($message));
        
        $m->published->setval(date('Y-m-d H:i:s'));
        $m->delivered->setval(date('Y-m-d H:i:s'));
    
        $m->run_query($m->query()->save($m));

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
        $query = RosterLink::query()->select()
                                 ->where(array(
                                            'key' => $this->user->getLogin(),
                                            'jid' => echapJid($jid)));
        $contacts = RosterLink::run_query($query);

        foreach($contacts as $contact) {
            if((int)$contact->getData('chaton') == 1 || (int)$contact->getData('chaton') == 2) {
                $contact->chaton->setval(0);

                $contact->run_query($contact->query()->save($contact));
            }
        }
	}
    
    function ajaxHideTalk($jid)
    {
        $query = RosterLink::query()->select()
                                 ->where(array(
                                            'key' => $this->user->getLogin(),
                                            'jid' => echapJid($jid)));
        $contact = RosterLink::run_query($query);
        $contact = $contact[0];
        
        if($contact->getData('chaton') == 1) {
            $contact->chaton->setval(2);
        }
        else {
            $contact->chaton->setval(1);
        }
        $contact->run_query($contact->query()->save($contact));
        
        RPC::call('scrollTalk',
                   'messages'.$contact->getData('jid'));
        RPC::commit();
    }
    
    function prepareMessage($message) {
        if($message->getData('body') != '') {
            $html = '<div class="message ';
                if($message->getData('key') == $message->getData('from'))
                    $html.= 'me';
                   
            $content = $message->getData('body');
                    
            if(preg_match("#^/me#", $message->getData('body'))) {
                $html .= " own ";
                $content = "** ".substr($message->getData('body'), 4);
            }
                    
            $html .= '"><span class="date">'.date('H:i', strtotime($message->getData('published'))).'</span>';
            $html.= prepareString(htmlentities($content, ENT_COMPAT, "UTF-8")).'</div>';
            return $html;
        } else {
            return '';
        }
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
            $tabstyle = ' style="display: none;" ';            
            $panelstyle = ' style="display: block;" ';
        }
        
        $html = '
            <div class="chat" onclick="this.querySelector(\'textarea\').focus()">
                <div class="panel" '.$panelstyle.'>
                    <div class="head" >
                        <span class="chatbutton cross" onclick="'.$this->genCallAjax("ajaxCloseTalk", "'".$contact->getData('jid')."'").' closeTalk(this)"></span>
                        <span class="chatbutton arrow" onclick="'.$this->genCallAjax("ajaxHideTalk", "'".$contact->getData('jid')."'").' hideTalk(this)"></span>
                        <img class="avatar"  src="'.Contact::getPhotoFromJid('xs', $contact->getData('jid')).'" />
                        <a class="name" href="?q=friend&f='.$contact->getData('jid').'">
                            '.$contact->getData('rostername').'
                        </a>
                        <div class="clear"></div>
                    </div>
                    <div class="messages" id="messages'.$contact->getData('jid').'">
                        '.$messageshtml.'
                        <div style="display: none;" class="message" id="composing'.$contact->getData('jid').'">'.t('Composing...').'</div>
                        <div style="display: none;" class="message" id="paused'.$contact->getData('jid').'">'.t('Paused...').'</div>
                    </div>
                    
                    <div class="text">
                         <textarea 
                            rows="1"
                            onkeyup="movim_textarea_autoheight(this);"
                            onkeypress="if(event.keyCode == 13) {'.$this->genCallAjax('ajaxSendMessage', "'".$contact->getData('jid')."'", "sendMessage(this, '".$contact->getData('jid')."')").' return false; }"
                            onfocus="setBackgroundColor(\'chatwindow'.$contact->getData('jid').'\', \'#444444\')"
                        ></textarea>
                    </div>
                </div>
                
                <div class="tab '.$tabclass.'" '.$tabstyle.' onclick="'.$this->genCallAjax("ajaxHideTalk", "'".$contact->getData('jid')."'").' showTalk(this);">
                    <div class="name">
                        <img class="avatar"  src="'.Contact::getPhotoFromJid('xs', $contact->getData('jid')).'" />'.$contact->getData('rostername').'
                    </div>
                </div>
            </div>
            ';
        return $html;
    }
    
    function build()
    {
        $query = RosterLink::query()
                                ->where(
                                    array(
                                        'RosterLink`.`key' => $this->user->getLogin(), 
                                        array(
                                            'chaton' => 
                                            array(1, '|2'))
                                    )
                                );
        $contacts = RosterLink::run_query($query);
        
        echo '<div id="chats">';
        if($contacts != false) {
            foreach($contacts as $contact) {
                echo $this->prepareChat($contact);
            }
        }
        echo '</div>';
    }
}
