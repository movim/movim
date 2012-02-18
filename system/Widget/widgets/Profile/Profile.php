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
        $this->registerEvent('mypresence', 'onMyVcardReceived');
    }
    
    function onMyVcardReceived($vcard = false)
    {
		$html = $this->prepareVcard($vcard);
        RPC::call('movim_fill', 'profile', RPC::cdata($html));
    }
    
	function ajaxSetStatus($statustext, $status)
	{
		$xmpp = Jabber::getInstance();
		$xmpp->setStatus($statustext, $status);
	}
    
    function prepareVcard($vcard = false)
    {
        $txt = array(
                1 => t('Online'),
                2 => t('Away'),
                3 => t('Do Not Disturb'),
                4 => t('Long Absence'),
            );
    
        global $sdb;
        $user = new User();
        $me = $sdb->select('Contact', array('key' => $user->getLogin(), 'jid' => $user->getLogin()));
        
		$xmpp = Jabber::getInstance();
        $presence = PresenceHandler::getPresence($user->getLogin(), true, $xmpp->getResource());
        
        if(isset($me[0])) {
            $me = $me[0];
            $html = '<h1>'.$me->getTrueName().'</h1>';
            $html .= '<img src="'.$me->getPhoto().'">';
            $html .= '<input type="text" id="status" value="'.$presence['status'].'"><br />';
            $html .= '
                <a 
                    onclick="showPresence(this)"
                    class="presence_button button tiny icon '.$presence['presence_txt'].'" 
                    href="#">&nbsp;'.$txt[$presence['presence']].'
                </a>
                <a 
                    onclick="'.$this->genCallAjax('ajaxSetStatus', "':D'", "'online'").'" 
                    style="display: none;";
                    class="presence_button button merged left tiny icon online" href="#">
                </a>
                <a 
                    onclick="'.$this->genCallAjax('ajaxSetStatus', "':D'", "'away'").'"
                    style="display: none;";
                    class="presence_button button merged tiny icon away" href="#">
                </a>
                <a 
                    onclick="'.$this->genCallAjax('ajaxSetStatus', "':D'", "'dnd'").'" 
                    style="display: none;";
                    class="presence_button button merged tiny icon dnd" href="#">
                </a>
                <a 
                    onclick="'.$this->genCallAjax('ajaxSetStatus', "':D'", "'xa'").'" 
                    style="display: none;";
                    class="presence_button button merged right tiny icon xa" href="#">
                </a>';
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
