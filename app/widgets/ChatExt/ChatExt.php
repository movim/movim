<?php

/**
 * @package Widgets
 *
 * @file ChatExt.php
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

require_once(APP_PATH . "widgets/Chat/Chat.php");

class ChatExt extends WidgetBase
{
    function WidgetLoad()
    {
        $this->addjs('chatext.js');
        //if($this->user->getConfig('chatbox') == 1) {
            $this->registerEvent('message', 'onMessage');
            $this->registerEvent('openchat', 'onEvent');
            $this->registerEvent('closechat', 'onEvent');
        //}
    }
    
    function prepareChat($contact)
    {
        $md = new \modl\MessageDAO();
        $messages = $md->getContact($contact->jid, 0, 10);
        
        $messageshtml ='';

        if(!empty($messages)) {
            $day = '';
            foreach($messages as $m) {
                if($day != date('d',strtotime($m->published))) {
                    $messageshtml .= '<div class="message presence">'.prepareDate(strtotime($m->published), false).'</div>';
                    $day = date('d',strtotime($m->published));
                }
                $chat = new Chat();
                $messageshtml .= $chat->prepareMessage($m);
            }
        }
        
        $style = '';
        if($contact->chaton == 2) {
            $tabstyle = ' style="display: none;" ';            
            $panelstyle = ' style="display: block;" ';
        }
        
        $html = '
            <div class="chat" onclick="this.querySelector(\'textarea\').focus()">
                <div class="messages" id="messages'.$contact->jid.'">
                    '.$messageshtml.'
                    <div style="display: none;" class="message" id="composing'.$contact->jid.'">'.t('Composing...').'</div>
                    <div style="display: none;" class="message" id="paused'.$contact->jid.'">'.t('Paused...').'</div>                        
                </div>
                
                <div class="text">
                     <textarea 
                        rows="1"
                        onkeyup="movim_textarea_autoheight(this);"
                        onkeypress="if(event.keyCode == 13) { self.opener.'.$this->genCallWidget('Chat','ajaxSendMessage', "'".$contact->jid."'", "sendMessage(this, '".$contact->jid."')").' return false; }"
                    ></textarea>
                </div>
                
            </div>
            ';
        return $html;
    }
    
    function prepareList($contact, $first = false)
    {
        if($first)
            $checked = ' checked ';
        $html = '
            <li>
                <input type="radio" name="contact" id="contact'.$contact->jid.'" '.$checked.'/>
                <label class="tab" for="contact'.$contact->jid.'" onclick="setTimeout(function() {scrollAllTalks()}, 100);">
                    <img class="avatar"  src="'.$contact->getPhoto('xs').'" />'.
                    $contact->getTrueName().'
                </label>
                <div class="content">'.trim($this->prepareChat($contact)).'
                    <span 
                        class="chatbutton cross" 
                        onclick="self.opener.'.$this->genCallWidget('Chat','ajaxCloseTalk', "'".$contact->jid."'").'"></span>
                </div>
            </li>';
        return $html;
    }
    
    function onEvent()
    {
        if(!Cache::c('chatpop')) {
            $html = $this->preparePop();
            RPC::call('popUpEvent', 'movim_fill', 'chatpop', $html);
            RPC::call('popUpEvent', 'scrollAllTalks');
        }
    }
    
    function preparePop()
    {
        $rc = new \modl\ContactDAO();
        $contacts = $rc->getRosterChat();
        
        $list = '';
        
        if(isset($contacts)) {
            $first = true;
            
            foreach($contacts as $contact) {
                $list .= $this->prepareList($contact, $first);
                $first = false;
            }
            $html = '<ul id="chatpoplist">'.$list.'</ul>';
        }
        
        return $html;
    }    
    
    function onMessage($message) 
    {
        if($message->session == $message->jidfrom) {
            $key = $message->jidfrom;
            $jid = $message->jidto;
        } else {
            $key = $message->jidto;
            $jid = $message->jidfrom;
        }
        
        $chatpop = Cache::c('chatpop');
        if(!$chatpop) {
            $chat = new Chat();
            RPC::call('popUpEvent', 'movim_append', 'messages'.$jid, $chat->prepareMessage($message));
            RPC::call('popUpEvent', 'scrollAllTalks');
        }
    }
    
    function build()
    {

    }
    
}
