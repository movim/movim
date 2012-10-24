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
        $presence = Cache::c('presence');
        Cache::c(
            'presence', 
            array(
                'status' => $presence['status'],
                'show' => $presence['show']
                )
        );
		$this->xmpp->logout();
	}
    
	function ajaxSetStatus($show)
	{
        // We update the cache with our status and presence
        $presence = Cache::c('presence');

        if($show == "boot") $show = $presence['show'];
        Cache::c(
            'presence', 
            array(
                'status' => $presence['status'],
                'show' => $show
                )
        );
        
        switch($show) {
            case 'chat':
                $p = new moxl\PresenceChat();
                $p->setStatus($presence['status'])->request();
                break;
            case 'away':
                $p = new moxl\PresenceAway();
                $p->setStatus($presence['status'])->request();
                break;
            case 'dnd':
                $p = new moxl\PresenceDND();
                $p->setStatus($presence['status'])->request();
                break;
            case 'xa':
                $p = new moxl\PresenceXA();
                $p->setStatus($presence['status'])->request();
                break;
        }
	}
    
    function preparePresence()
    {
        $txt = getPresences();
    
        global $session;
        $query = Presence::query()->select()
                           ->where(array(
                                   'key' => $this->user->getLogin(),
                                   'jid' => $this->user->getLogin(),
                                   'ressource' => $session['ressource']))
                           ->limit(0, 1);
        $data = Presence::run_query($query);

        if($data)
            $presence = $data[0]->getPresence();
        
        $html = '<div id="logouttab" class="'.$presence['presence_txt'].'" onclick="showLogoutList();">'.$txt[$presence['presence']].'</div>';
                
        $html .= '
            <div id="logoutlist">
                <a onclick="'.$this->genCallAjax('ajaxSetStatus', "'chat'").'; hideLogoutList();" class="online">'.$txt[1].'</a>
                <a onclick="'.$this->genCallAjax('ajaxSetStatus', "'away'").'; hideLogoutList();" class="away">'.$txt[2].'</a>
                <a onclick="'.$this->genCallAjax('ajaxSetStatus', "'dnd'").'; hideLogoutList();" class="dnd">'.$txt[3].'</a>
                <a onclick="'.$this->genCallAjax('ajaxSetStatus', "'xa'").'; hideLogoutList();" class="xa">'.$txt[4].'</a>
                <!--<a onclick="'.$this->genCallAjax('ajaxLogout').'; setTimeout(\'window.location.reload()\', 2000);">'.$txt[5].'</a>-->
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
