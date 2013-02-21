<?php

/**
 * @package Widgets
 *
 * @file Profile.php
 * This file is part of MOVIM.
 *
 * @brief The Profile widget
 *
 * @author Timothée	Jaussoin <edhelas_at_gmail_dot_com>
 *
 * @version 1.0
 * @date 20 October 2010
 *
 * Copyright (C)2010 MOVIM project
 *
 * See COPYING for licensing information.
 */

class Profile extends WidgetCommon
{

    private static $status;

    function WidgetLoad()
    {
        $this->addcss('profile.css');
        $this->addjs('profile.js');
        $this->registerEvent('myvcard', 'onMyVcardReceived');
        $this->registerEvent('mypresence', 'onMyPresence');
    }
    
    function onMyVcardReceived($vcard = false)
    {
		$html = $this->prepareVcard($vcard);
        RPC::call('movim_fill', 'profile', RPC::cdata($html));
    }
    
    function onMyPresence()
    {
        RPC::call('movim_fill', 'statussaved', RPC::cdata('✔ '.t('Saved')));        
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
                $p = new moxl\PresenceChat();
                $p->setStatus($status)->request();
                break;
            case 'away':
                $p = new moxl\PresenceAway();
                $p->setStatus($status)->request();
                break;
            case 'dnd':
                $p = new moxl\PresenceDND();
                $p->setStatus($status)->request();
                break;
            case 'xa':
                $p = new moxl\PresenceXA();
                $p->setStatus($status)->request();
                break;
            default :
                $p = new moxl\PresenceChat();
                $p->setStatus($status)->request();
                break;
        }
    }
    
    function prepareVcard($vcard = false)
    {
        $cd = new modl\ContactDAO();
        $contact = $cd->get($this->user->getLogin());
        
        $presence = Cache::c('presence');
        
        if(isset($contact)) {
            $me = $contact;

            // My avatar
            $html .= '
            <a href="?q=friend&f='.$this->user->getLogin().'">
                <div class="block avatar">
                    <img src="'.$me->getPhoto('l').'"/>
                </div>
            </a>';
                
            // Contact general infos
            $html .= '
                <div class="block">
                    <h1 style="text-decoration: none;">'.$me->getTrueName().'</h1><br />';

            if($this->testIsSet($me->name))
                $html .= $me->name.' ';
            else
                $html .= $me->getTrueName().' ';
                
            if($this->testIsSet($me->url))
                $html .= '<br /><a target="_blank" href="'.$me->url.'">'.$me->url.'</a>';
            

            $html .= '<br /><br />
                </div>
            ';
                
            $html .= '
            <div class="block" style="width: 550px;">
                <div class="textbubble">
                    <textarea 
                        id="status" 
                        spellcheck="false"
						onfocus="this.style.fontStyle=\'italic\'; this.parentNode.querySelector(\'#statussaved\').innerHTML = \'\'"
						onblur="this.style.fontStyle=\'normal\';"
                        onkeypress="if(event.keyCode == 13) {'.$this->genCallAjax('ajaxSetStatus', 'encodeURIComponent(this.value)').'; this.blur(); return false;}"
                        onload="movim_textarea_autoheight(this);"
                        onkeyup="movim_textarea_autoheight(this);">'.$presence['status'].'</textarea>
                    <div id="statussaved" style="text-align: right;"></div>
                </div>
            </div>
                ';
        } else {
			$html .= '
                <div class="block">
                    '.t('No profile yet ?').'<br /><br />
                    <a class="button icon add" style="text-shadow: none; color: rgb(43, 43, 43); text-decoration: none;" href="?q=profile">'.t("Create my vCard").'</a><br /><br />
                </div>';
		}
        
        return $html;
    }
    
    function build()
    {
    ?>
    
        <div id="profile">
			<?php 
				echo $this->prepareVcard();
			?>
        </div>
    <?php
    }
}
