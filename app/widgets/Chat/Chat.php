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
use Moxl\Xec\Action\Message\Composing;
use Moxl\Xec\Action\Message\Paused;
use Moxl\Xec\Action\Message\Publish;
use Moxl\Xec\Action\Presence\Unavaiable;
 
class Chat extends WidgetBase {

    private $_encrypted = false;

    function load() {
        $this->addcss('chat.css');
        $this->addjs('chat.js');
        $this->registerEvent('message', 'onMessage');
        $this->registerEvent('messagepublished', 'onMessagePublished');
        $this->registerEvent('composing', 'onComposing');
        $this->registerEvent('paused', 'onPaused');
        $this->registerEvent('attention', 'onAttention');
        $this->registerEvent('presence', 'onPresence');
        $this->registerEvent('presencemuc', 'onPresenceMuc');
    }

    function display() {
        $this->view->assign('chats', $this->prepareChats());
    }

    function onPresence($packet) {
        $presence = $packet->content;
        $arr = $presence->getPresence();

        $txt = getPresences();
            
        if($presence->isChatroom())
            RPC::call('movim_fill', 'list' . $arr['jid'], $this->prepareMucList($arr['jid']));
        else {
            $rc = new \modl\ContactDAO;
            $contact = $rc->getRosterItem(echapJid($arr['jid']));
            if(isset($contact) && $contact->chaton > 0 ) {
                $html='
                    <div class="message presence">
                        <span class="date">' . date('G:i', time()) . '</span>' . prepareString(htmlentities($txt[$arr['presence']] . ' - ' . $arr['ressource'], ENT_COMPAT, "UTF-8")) . '
                    </div>';
                    
                RPC::call('movim_append', 'messages' . $arr['jid'], $html);
                RPC::call('scrollTalk', 'messages' . $arr['jid']);
            }
        }
    }

    function onPresenceMuc($toggle) {
        if($toggle) {
            Notification::appendNotification($this->__('Connected to the chatroom'), 'success');
        } else {
            Notification::appendNotification($this->__('Disconnected to the chatroom'), 'success');
        }
        
        RPC::call('movim_fill', 'chats', $this->prepareChats());
        RPC::call('scrollAllTalks');
    }
    
    private function checkEncrypted($message) {
        if(preg_match("#^\?OTR#", $message->body)) {
            $this->_encrypted = true;
            $message->body = $this->__('message.encrypted');
        }
        
        return $message;
    } 

    function onMessage($message) {
        if($message->session == $message->jidfrom) {
            $key = $message->jidfrom;
            $jid = $message->jidto;
        } else {   
            $key = $message->jidto;
            $jid = $message->jidfrom;
        }
        
        $rd = new \modl\RosterLinkDAO();
        $rc = new \modl\ContactDAO();
        
        $contact = $rc->getRosterItem(echapJid($jid));
        
        $message = $this->checkEncrypted($message);
        
        if($contact != null
        && $message->session != $message->jidfrom
        && $this->_encrypted == false) {
            RPC::call(
                'notify',
                $contact->getTrueName(),
                $message->body,
                $contact->getPhoto('m'));
        }

        $this->_encrypted = false;
        
        if($contact != null && $contact->chaton == 0) {
            $contact->chaton = 2;
            $rd->setChat($jid, 2);
            
            RPC::call('movim_prepend', 'chats', $this->prepareChat($contact));
            RPC::call('scrollAllTalks');
        } elseif($contact != null && $message->body != '') {
            $html = $this->prepareMessage($message);
            if($contact->chaton == 1) {
                RPC::call('colorTalk', 'messages' . $contact->jid);
            }
            RPC::call('movim_append', 'messages' . $contact->jid, $html);

            //if($message->session != $message->jidfrom) {
                RPC::call('hideComposing', $contact->jid);
                RPC::call('hidePaused', $contact->jid);
            //}
            
            RPC::call('scrollTalk', 'messages' . $contact->jid);
        }

        // Muc case
        elseif($message->ressource != '') {
            $html = $this->prepareMessage($message, true);
            RPC::call('movim_append', 'messages' . $message->jidfrom, $html);
            RPC::call('scrollTalk', 'messages' . $message->jidfrom);
        }
        RPC::commit();
    }

    function onMessagePublished($jid) {
        Notification::appendNotification($this->__('message.published'), 'success');
    }

    function onComposing($jid) {
        $rd = new \Modl\RosterLinkDAO();
        
        $contact = $rd->get(echapJid($jid));
        
        if(isset($contact) && in_array($contact->chaton, array(1, 2))) {
            RPC::call('showComposing', $contact->jid);
            RPC::call('scrollTalk', 'messages' . $contact->jid);
        }
    }

    function onPaused($jid) {
        $rd=new \Modl\RosterLinkDAO();
        
        $contact=$rd->get(echapJid($jid));
        
        if(in_array($contact->chaton, array(1, 2))) {
            RPC::call('showPaused', $contact->jid);
            RPC::call('scrollTalk', 'messages' . $contact->jid);
        }
    }

    function onAttention($packet) {
        $jid = $packet->from;
        $rc = new \Modl\ContactDAO();
        $contact = $rc->getRosterItem(echapJid($jid));
        $html = '
            <div style="font-weight: bold; color: black;" class="message" >
                <span class="date">' . 
                    date('G:i', time()) . '</span>' . 
                    $this->__('chat.attention', $contact->getTrueName()) . '
            </div>';
        RPC::call('movim_append', 'messages' . $jid, $html);
        RPC::call('scrollTalk', 'messages' . $jid);
    }
    /**
     * Open a new talk
     *
     * @param string $jid
     * @return void
     */
    function ajaxOpenTalk($jid) {
        $rc = new \Modl\ContactDAO();
        $contact = $rc->getRosterItem(echapJid($jid));
        
        if(isset($contact) && $contact->chaton == 0 && !in_array($contact->presence, array(5, 6))) {
            $contact->chaton = 2;
            $rd = new \Modl\RosterLinkDAO();
            $rd->setChat(echapJid($jid), 2);
            RPC::call('movim_prepend', 'chats', $this->prepareChat($contact));
            RPC::call('scrollAllTalks');
            RPC::commit();
        }
    }
    /**
     * Send an encrypted message
     *
     * @param string $to
     * @param string $message
     * @return void
     */
    function ajaxSendEncryptedMessage($to, $message, $muc=false, $ressource=false) {
        $this->ajaxSendMessage($to, $message, $muc, $ressource, true);
    }
    /**
     * Send a message
     *
     * @param string $to
     * @param string $message
     * @return void
     */
    function ajaxSendMessage($to, $message, $muc=false, $ressource=false, $encrypted=false) {
        if($message == '')
            return;
        
        $m=new \Modl\Message();
        $m->session = $this->user->getLogin();
        $m->jidto   = echapJid($to);
        $m->jidfrom = $this->user->getLogin();
        
        $session    = \Sessionx::start();
        
        $m->type    = 'chat';
        $m->ressource = $session->ressource;
        
        if($muc) {
            $m->type        = 'groupchat';
            $m->ressource   = $session->user;
            $m->jidfrom     = $to;
        }
        
        $m->body      = rawurldecode($message);
        $m->published = date('Y-m-d H:i:s');
        $m->delivered = date('Y-m-d H:i:s');
        
        $md = new \Modl\MessageDAO();
        $md->set($m);
        
        $evt = new Event();
        $evt->runEvent('message', $m);

        if($ressource!=false) {
            $to = $to . '/' . $ressource;
        }

        // We decode URL codes to send the correct message to the XMPP server
        $m = new Publish;
        $m->setTo($to);
        $m->setContent(htmlspecialchars(rawurldecode($message)));

        //->setEncrypted($encrypted)->setContent(htmlspecialchars(rawurldecode($message)));
        if($muc) {
            $m->setMuc();
        }
        $m->request();
    }
    
    /**
     * Send a "composing" message
     * 
     * @param string $to
     * @return void
     */
    function ajaxSendComposing($to) {
        $mc = new Composing;
        $mc->setTo($to)->request();
    }
    /**
     * Send a "paused" message
     * 
     * @param string $to
     * @return void
     */
    function ajaxSendPaused($to) {
        $mp=new Paused;
        $mp->setTo($to)->request();
    }
    /**
     * Close a talk
     *
     * @param string $jid
     * @return void
     */
    function ajaxCloseTalk($jid) {
        $rd = new \Modl\RosterLinkDAO();
        $contact = $rd->get(echapJid($jid));
        $contact->chaton = 0;
        $rd->setNow($contact);
        RPC::call('movim_delete', 'chat' . echapJid($jid));
        RPC::commit();
    }

    function ajaxHideTalk($jid) {
        $rd = new \Modl\RosterLinkDAO();
        $contact = $rd->get(echapJid($jid));
        
        if($contact->chaton == 1) {
            $contact->chaton = 2;
        } else {
            $contact->chaton = 1;
        }
        $rd->setNow($contact);
        RPC::call('scrollTalk', 'messages' . $contact->jid);
        RPC::commit();
    }
    /**
     * Exit a muc
     *
     * @param $jid
     * @return void
     */
    function ajaxExitMuc($jid, $ressource) {
        $pu = new Unavaiable;
        $pu->setTo($jid)->setRessource($ressource)->request();
    }

    function prepareMessage($message, $muc = false) {
        if($message->body != '' || $message->subject != '') {
            if($message->subject != '') {
                $message->body = $message->subject;
            }
            
            $message = $this->checkEncrypted($message);
            
            $html='
                <div class="message ';
                if($message->session == $message->jidfrom) {
                    $html .= 'me';
                }
                
                if(isset($message->html)) {
                    $type = 'html';
                    $content = $message->html;
                } else {
                    $type='';
                    $content = prepareString(
                        htmlentities($message->body, ENT_COMPAT, "UTF-8"));
                }
                if(preg_match("#^/me#", $message->body)) {
                    $html .= ' own ';
                    $content = '** ' . substr($message->body, 4);
                }
                
                $c = new \modl\Contact();
            $html .= '">
                <img class="avatar" src="' . $c->getPhoto('xs', $message->jidfrom) . '" alt="avatar"/>
                <span class="date">' . date('H:i', strtotime($message->published)) . '</span>';
                
            if($muc != false) {
                $html.='
                    <span
                        class="ressource ' . $this->colorNameMuc($message->ressource) . '">' .
                        $message->ressource . '
                    </span>';
            }
            $html .= '<div class="content ' . $type . '">' . $content . '</div>
                </div>';
            return $html;
        } else {
            return '';
        }
    }

    function prepareChats() {
        $rc = new \modl\ContactDAO();
        $contacts = $rc->getRosterChat();
        $html = '';

        // Another filter to fix the database request
        $check = array();
        if(isset($contacts)) {
            foreach($contacts as $contact) {
                if(!in_array($contact->jid, $check)) {
                    $html .= trim($this->prepareChat($contact));

                    //$jid = $contact->jid;
                    array_push($check, $contact->jid);
                }
            }
        }

        // And we show the subscribed conferences
        $cd = new \Modl\ConferenceDAO();
        $cs = $cd->getConnected();
        
        if($cs) {
            foreach($cs as $c) {
                $html .= trim($this->prepareMuc($c));
            }
        }
        
        $html.='<div class="filler"></div>';
        return $html;
    }

    // Prepare Chat
    function prepareChat($contact) {
        $md = new \Modl\MessageDAO();
        $messages = $md->getContact(echapJid($contact->jid), 0, 10);
        $messageshtml = '';
        if($messages != null) {
            $messages = array_reverse($messages);
            $day = '';
            foreach($messages as $m) {
                if($day != date('d', strtotime($m->published))) {
                    $messageshtml.='<div class="message presence">' . prepareDate(strtotime($m->published), false) . '</div>';
                    $day = date('d', strtotime($m->published));
                }
                $messageshtml .= $this->prepareMessage($m);
            }
        }
        
        $style = '';
        $panelstyle = '';
        $tabstyle = '';
        
        if($contact->chaton == 2) {
            $tabstyle=' style="display: none;" ';
            $panelstyle=' style="display: block;" ';
        }
        
        $chatview = $this->tpl();
        $chatview->assign(
            'send',
            $this->genCallAjax(
                'ajaxSendMessage',
                "'" . $contact->jid . "'",
                "sendMessage(this, '" . $contact->jid . "')",
                "false",
                "'" . $contact->ressource . "'"));
                
        $chatview->assign('contact', $contact);
        $chatview->assign('tabstyle', $tabstyle);
        $chatview->assign('panelstyle', $panelstyle);
        $chatview->assign('messageshtml', $messageshtml);
        $chatview->assign('closetalk', $this->genCallAjax("ajaxCloseTalk", "'" . $contact->jid . "'"));
        $chatview->assign('hidetalk', $this->genCallAjax("ajaxHideTalk", "'" . $contact->jid . "'"));
        $chatview->assign('composing', $this->genCallAjax('ajaxSendComposing', "'" . $contact->jid . "'"));
        $chatview->assign('paused', $this->genCallAjax('ajaxSendPaused', "'" . $contact->jid . "'"));
        
        $html=$chatview->draw('_chat_contact', true);
        
        return $html;
    }

    function prepareMuc($conference) {
        $jid = $conference->conference;

        // Zeu messages
        $md = new \Modl\MessageDAO();
        
        $messages = $md->getContact($jid, 0, 10);
        $messageshtml = '';
        if(!empty($messages)) {
            $messages = array_reverse($messages);
            $day = '';
            foreach($messages as $m) {
                if($day != date('d', strtotime($m->published))) {
                    $messageshtml .=
                        '<div class="message presence">' .
                            prepareDate(strtotime($m->published), false) .
                        '</div>';
                    $day = date('d', strtotime($m->published));
                }
                $messageshtml.=$this->prepareMessage($m, true);
            }
        }
        
        $mucview = $this->tpl();
        $mucview->assign('jid', $jid);
        $mucview->assign('messageshtml', $messageshtml);
        $mucview->assign('toggle', $this->genCallAjax("ajaxToggleMuc", "'" . $jid . "'"));
        $mucview->assign(
            'sendmessage',
            $this->genCallAjax(
                'ajaxSendMessage',
                "'" . $jid . "'",
                 "sendMessage(this, '" . $jid . "')", "true"));
        
        $session = \Sessionx::start();
        $mucview->assign('exitmuc', $this->genCallAjax("ajaxExitMuc", "'" . $jid . "'", "'" . $session->username . "'"));
        $sess = \Session::start(APP_NAME);
        $state = $sess->get(md5('muc' . $jid));
        $mucview->assign('conference', $conference);
        $mucview->assign('muclist', $this->prepareMucList($jid));
        
        if($state == 1) {
            $mucview->assign('tabstyle', 'style="display: none;"');
            $mucview->assign('panelstyle', 'style="display: block;"');
        } else {
            $mucview->assign('tabstyle', '');
            $mucview->assign('panelstyle', '');
        }
        $html = $mucview->draw('_chat_muc', true);
        
        return $html;
    }

    function prepareMucList($jid) {
        // Zeu muc list
        $pd = new \modl\PresenceDAO();
        $presences = $pd->getJid($jid);
        $muclist = $this->tpl();
        $muclist->assign('muclist', $presences);
        
        return $muclist->draw('_chat_muc_list', true);
    }

    function ajaxToggleMuc($jid) {
        $hash = md5('muc' . $jid);
        $sess = \Session::start(APP_NAME);
        $state = $sess->get($hash);
        
        if($state == 1) {
            $sess->set($hash, 0);
        } else {
            $sess->set($hash, 1);
        }
    }

    function colorNameMuc($ressource) {
        $colors = array(
            0 => 'purple',
            1 => 'purple',
            2 => 'wine',
            3 => 'yellow',
            4 => 'orange',
            5 => 'green',
            6 => 'red',
            7 => 'blue');
            
        $s = base_convert(sha1($ressource), 16, 8);
        
        if(isset($s[5])) {
            return $colors[(int) $s[5]];
        } else {
            return 'orange';
        }
    }
}
