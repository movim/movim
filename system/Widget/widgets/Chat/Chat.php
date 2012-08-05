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

        $query = Contact::query()->select()
                                 ->where(array(
                                            'key' => $key,
                                            'jid' => $jid));
        $contact = Contact::run_query($query);

        $contact = $contact[0];
        
        if($contact->getData('chaton') == 0) {
            RPC::call('movim_prepend',
                           'chats',
                           RPC::cdata($this->prepareChat($contact)));
            RPC::call('scrollAllTalks');
            $contact->chaton->setval(1);
            
            $contact->run_query($contact->query()->save($contact));
        } else if($message->getData('body') != '') {
            
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

            RPC::call('hidePaused',
                           $contact->getData('jid')); 
                           
            RPC::call('scrollTalk',
                           'messages'.$contact->getData('jid'));
                           
            RPC::call('newMessage');
            

        }            
        
        RPC::commit();

    }
    
    function onComposing($jid)
    {
        $query = Contact::query()->select()
                                 ->where(array(
                                            'key' => $this->user->getLogin(),
                                            'jid' => $jid));
        $contact = Contact::run_query($query);
        $contact = $contact[0];
        
        if($contact->getData('chaton') == 1) {
            RPC::call('showComposing',
                       $contact->getData('jid'));
                           
            RPC::call('scrollTalk',
                      'messages'.$contact->getData('jid'));
        }
    }

    function onPaused($jid)
    {
        $query = Contact::query()->select()
                                 ->where(array(
                                            'key' => $this->user->getLogin(),
                                            'jid' => $jid));
        $contact = Contact::run_query($query);
        $contact = $contact[0];
        
        if($contact->getData('chaton') == 1) {
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
        $query = Contact::query()->select()
                                 ->where(array(
                                            'key' => $this->user->getLogin(),
                                            'jid' => $jid));
        $contact = Contact::run_query($query);
        $contact = $contact[0];
        

        
        if($contact->getData('chaton') != 1) {
            RPC::call('movim_prepend',
                           'chats',
                           RPC::cdata($this->prepareChat($contact)));
            RPC::call('scrollAllTalks');

            $contact->chaton->setval(1);
            
            $contact->run_query($contact->query()->save($contact));

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
        $m->to->setval($to);
        $m->from->setval($this->user->getLogin());
        
        $m->type->setval("chat");
        
        $m->body->setval(rawurldecode($message));
        
        $m->published->setval(date('Y-m-d H:i:s'));
        $m->delivered->setval(date('Y-m-d H:i:s'));
    
        $m->run_query($m->query()->save($m));

        $this->onMessage($m);
             
		// We decode URL codes to send the correct message to the XMPP server
        moxl\message($to, rawurldecode($message));
    }
    
	/**
	 * Close a talk
	 *
	 * @param string $jid
	 * @return void
	 */
	function ajaxCloseTalk($jid) 
	{        
        $query = Contact::query()->select()
                                 ->where(array(
                                            'key' => $this->user->getLogin(),
                                            'jid' => $jid));
        $contact = Contact::run_query($query);
        $contact = $contact[0];
        
        if($contact->getData('chaton') == 1 || $contact->getData('chaton') == 2) {
            $contact->chaton->setval(0);
            
            $contact->run_query($contact->query()->save($contact));
        }
	}
    
    function ajaxHideTalk($jid)
    {
        $query = Contact::query()->select()
                                 ->where(array(
                                            'key' => $this->user->getLogin(),
                                            'jid' => $jid));
        $contact = Contact::run_query($query);
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
            <div class="chat" onclick="this.querySelector(\'textarea\').focus()">'.
                '<div class="messages" '.$style.' id="messages'.$contact->getData('jid').'">'.$messageshtml.'
                    <div style="display: none;" class="message" id="composing'.$contact->getData('jid').'">'.t('Composing...').'</div>
                    <div style="display: none;" class="message" id="paused'.$contact->getData('jid').'">'.t('Paused...').'</div>
                 </div>'.
                '<textarea onkeyup="movim_textarea_autoheight(this);"  '.$style.'
                    onkeypress="if(event.keyCode == 13) {'.$this->genCallAjax('ajaxSendMessage', "'".$contact->getData('jid')."'", "sendMessage(this, '".$contact->getData('jid')."')").' return false;}"
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
