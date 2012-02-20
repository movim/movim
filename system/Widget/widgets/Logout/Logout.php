<?php

/**
 * @package Widgets
 *
 * @file Logout.php
 * This file is part of MOVIM.
 * 
 * @brief The little logout widget.
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

class Logout extends WidgetBase
{
    
    function WidgetLoad()
    {
    	$this->addcss('logout.css');
        $this->addjs('logout.js');
		$this->registerEvent('postdisconnected', 'onPostDisconnect');
        $this->registerEvent('serverdisconnect', 'onPostDisconnect'); // When you're kicked out
        $this->registerEvent('mypresence', 'onMyPresence');
    }
    
    function onMyPresence()
    {
		$html = $this->preparePresence();
        RPC::call('movim_fill', 'logout', RPC::cdata($html));
        RPC::commit();
    }

    function onPostDisconnect($data)
    {
	    RPC::call('movim_reload',
                       RPC::cdata(BASE_URI."index.php?q=disconnect"));
    }

	function ajaxLogout()
	{
		$this->xmpp->logout();
	}
    
	function ajaxSetStatus($statustext, $status)
	{
		$this->xmpp->setStatus($statustext, $status);
	}
    
    function preparePresence()
    {
        $txt = array(
                1 => t('Online'),
                2 => t('Away'),
                3 => t('Do Not Disturb'),
                4 => t('Extended Away'),
                5 => t('Logout')
            );
    
        global $sdb;
        $me = $sdb->select('Contact', array('key' => $this->user->getLogin(), 'jid' => $this->user->getLogin()));
        
        $presence = PresenceHandler::getPresence($this->user->getLogin(), true, $this->xmpp->getResource());
        
        $html = '<div id="logouttab" class="'.$presence['presence_txt'].'" onclick="showLogoutList();">'.$txt[$presence['presence']].'</div>';
                
        $html .= '
            <div id="logoutlist">
                <a onclick="'.$this->genCallAjax('ajaxSetStatus', "':D'", "'online'").'; hideLogoutList();" class="online">'.$txt[1].'</a>
                <a onclick="'.$this->genCallAjax('ajaxSetStatus', "':D'", "'away'").'; hideLogoutList();" class="away">'.$txt[2].'</a>
                <a onclick="'.$this->genCallAjax('ajaxSetStatus', "':D'", "'dnd'").'; hideLogoutList();" class="dnd">'.$txt[3].'</a>
                <a onclick="'.$this->genCallAjax('ajaxSetStatus', "':D'", "'xa'").'; hideLogoutList();" class="xa">'.$txt[4].'</a>
                <a onclick="'.$this->genCallAjax('ajaxLogout').'">'.$txt[5].'</a>
            </div>
                ';
        
        return $html;
    }

    function build()
    {
        ?>
        <div id="logout">
            <?php echo $this->preparePresence(); ?>
        </div>
        <?php
    }
}

?>
