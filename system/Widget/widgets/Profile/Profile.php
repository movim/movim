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

class Profile extends WidgetBase
{

    private static $status;

    function WidgetLoad()
    {
        $this->addcss('profile.css');
        $this->addjs('profile.js');
        $this->registerEvent('myvcard', 'onMyVcardReceived');
    }
    
    function onMyVcardReceived($vcard = false)
    {
		$html = $this->prepareVcard($vcard);
        RPC::call('movim_fill', 'profile', RPC::cdata($html));
    }
    
	function ajaxSetStatus($status)
	{
        $status = rawurldecode($status);
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
        }
		//$this->xmpp->setStatus(rawurldecode($status), $presence['show']);
	}
    
    function prepareVcard($vcard = false)
    {
        $query = Contact::query()->select()
                                 ->where(array(
                                            'key' => $this->user->getLogin(),
                                            'jid' => $this->user->getLogin()));
        $contact = Contact::run_query($query);
        
        $presence = Cache::c('presence');
        
        if(isset($contact[0])) {
            $me = $contact[0];
            $html ='
                <a href="?q=friend&f='.$this->user->getLogin().'">
                    <h1>'.$me->getTrueName().'</h1>
                    <img src="'.$me->getPhoto().'"/>
                </a>';
            $html .= '
                <div class="textbubble">
                    <textarea 
                        id="status" 
                        spellcheck="false"
                        onkeypress="if(event.keyCode == 13) {'.$this->genCallAjax('ajaxSetStatus', "getStatusText()").' return false;}"
                        onload="movim_textarea_autoheight(this);"
                        onkeyup="movim_textarea_autoheight(this);">'.$presence['status'].'</textarea>
                </div>
                <br />
                ';
        } else {
			$html .= t('No profile yet ?').'<br /><br />';
			$html .= '<a class="button icon add" style="padding-left: 25px;" href="?q=profile">'.t("Create my vCard").'</a><br /><br />';
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
