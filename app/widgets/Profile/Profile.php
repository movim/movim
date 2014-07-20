<?php

/**
 * @package Widgets
 *
 * @file Profile.php
 * This file is part of MOVIM.
 *
 * @brief The Profile widget
 *
 * @author Timothée    Jaussoin <edhelas_at_gmail_dot_com>
 *
 * @version 1.0
 * @date 20 October 2010
 *
 * Copyright (C)2010 MOVIM project
 *
 * See COPYING for licensing information.
 */

use Moxl\Xec\Action\Presence\Chat;
use Moxl\Xec\Action\Presence\Away;
use Moxl\Xec\Action\Presence\DND;
use Moxl\Xec\Action\Presence\XA;

class Profile extends WidgetCommon
{
    private static $status;

    function load()
    {
        $this->addcss('profile.css');
        $this->addjs('profile.js');
        $this->registerEvent('myvcard', 'onMyVcardReceived');
        $this->registerEvent('mypresence', 'onMyPresence');
    }
    
    function onMyVcardReceived($vcard = false)
    {
        $html = $this->prepareVcard($vcard);
        RPC::call('movim_fill', 'profile', $html);
    }
    
    function onMyPresence()
    {
        RPC::call('movim_fill', 'statussaved', '✔ '.$this->__('status.saved')); 
        Notification::appendNotification($this->__('status.updated'), 'success');
    }
    
    function ajaxSetStatus($status)
    {
        $status = htmlspecialchars(rawurldecode($status));
        // We update the cache with our status and presence
        $presence = Cache::c('presence');
        Cache::c(
            'presence', 
            array(
                'status' => $status,
                'show' => $presence['show'],
                )
        );
        
        switch($presence['show']) {
            case 'chat':
                $p = new Chat;
                $p->setStatus($status)->request();
                break;
            case 'away':
                $p = new Away;
                $p->setStatus($status)->request();
                break;
            case 'dnd':
                $p = new DND;
                $p->setStatus($status)->request();
                break;
            case 'xa':
                $p = new XA;
                $p->setStatus($status)->request();
                break;
            default :
                $p = new Chat;
                $p->setStatus($status)->request();
                break;
        }
    }
    
    function prepareVcard($vcard = false)
    {
        $cd = new modl\ContactDAO();
        $contact = $cd->get($this->user->getLogin());
        
        $presence = Cache::c('presence');
        
        $html = '';
        
        if(isset($contact)) {
            $me = $contact;

            // My avatar
            $html .= '
            <a
                class="avatar"
                style="background-image: url('.$me->getPhoto('l').');"
                href="'.Route::urlize('friend',$this->user->getLogin()).'">
            </a>';
                
            // Contact general infos
            $html .= '
                    <h1 class="padded" style="text-decoration: none;">'.$me->getTrueName().'</h1>';
                    
            $html .= '
                <div class="textbubble">
                    <textarea 
                        id="status" 
                        spellcheck="false"
                        placeholder="'.$this->__('status.here').'"
                        onfocus="this.style.fontStyle=\'italic\'; this.parentNode.querySelector(\'#statussaved\').innerHTML = \'\'"
                        onblur="this.style.fontStyle=\'normal\';"
                        onkeypress="if(event.keyCode == 13) {'.$this->genCallAjax('ajaxSetStatus', 'encodeURIComponent(this.value)').'; this.blur(); return false;}"
                        onload="movim_textarea_autoheight(this);"
                        onkeyup="movim_textarea_autoheight(this);">'.$presence['status'].'</textarea>
                    <div id="statussaved" style="text-align: right;"></div>
                </div>
                ';
        } else {
            $html .= '
                    '.t('No profile yet ?').'<br /><br />
                    <a 
                        class="button color green icon add" 
                        style="color: white;"
                        href="'.Route::urlize('profile').'">'.t("Create my vCard").'</a>';
        }
        
        return $html;
    }
}
