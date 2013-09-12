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

class Presence extends WidgetBase
{
    
    function WidgetLoad()
    {
        $this->addcss('presence.css');
        $this->registerEvent('mypresence', 'onMyPresence');
    }
    
    function onMyPresence()
    {
        $html = $this->preparePresence();
        RPC::call('movim_fill', 'logout', $html);
        RPC::commit();
    }

    function onPostDisconnect($data)
    {
        RPC::call('movim_reload',
                       BASE_URI."index.php?q=disconnect");
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
    
    function ajaxLogout()
    {
        $p = new moxl\PresenceUnavaiable();
        $p->request();
        //$user = new User();
        //$user->desauth();
        RPC::call('movim_redirect', Route::urlize('disconnect')); 
        RPC::commit();
    }
    
    function preparePresence()
    {
        $txt = getPresences();
        $txts = getPresencesTxt();
    
        global $session;
        
        $pd = new \modl\PresenceDAO();
        $p = $pd->getPresence($this->user->getLogin(), $session['ressource']);

        if($p)
            $html = '
                <div 
                    id="logouttab" 
                    class="'.$txts[$p->value].'"
                    onclick="movim_toggle_class(\'#logoutlist\', \'show\');">'.
                    $txt[$p->value].'
                </div>';
        else
            $html = '
                <div 
                    id="logouttab" 
                    class="'.$txts[1].'"
                    onclick="movim_toggle_class(\'#logoutlist\', \'show\');">'.
                    $txt[1].'
                </div>';
                
        $html .= '
            <div id="logoutlist">
                <a onclick="'.$this->genCallAjax('ajaxSetStatus', "'chat'").'; movim_toggle_class(\'#logoutlist\', \'show\');" class="online">'.$txt[1].'</a>
                <a onclick="'.$this->genCallAjax('ajaxSetStatus', "'away'").'; movim_toggle_class(\'#logoutlist\', \'show\');" class="away">'.$txt[2].'</a>
                <a onclick="'.$this->genCallAjax('ajaxSetStatus', "'dnd'").';  movim_toggle_class(\'#logoutlist\', \'show\');" class="dnd">'.$txt[3].'</a>
                <a onclick="'.$this->genCallAjax('ajaxSetStatus', "'xa'").';   movim_toggle_class(\'#logoutlist\', \'show\');" class="xa">'.$txt[4].'</a>
                <a onclick="'.$this->genCallAjax('ajaxLogout').';              movim_toggle_class(\'#logoutlist\', \'show\');" class="disconnect">'.t('Disconnect').'</a>
            </div>
                ';
        //href="'.Route::urlize('disconnect').'"
        
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
