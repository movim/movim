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

use Moxl\Xec\Action\Presence\Chat;
use Moxl\Xec\Action\Presence\Away;
use Moxl\Xec\Action\Presence\DND;
use Moxl\Xec\Action\Presence\XA;
use Moxl\Xec\Action\Presence\Unavaiable;

class Presence extends WidgetBase
{
    
    function load()
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
                $p = new Chat;
                $p->setStatus($presence['status'])->request();
                break;
            case 'away':
                $p = new Away;
                $p->setStatus($presence['status'])->request();
                break;
            case 'dnd':
                $p = new DND;
                $p->setStatus($presence['status'])->request();
                break;
            case 'xa':
                $p = new XA;
                $p->setStatus($presence['status'])->request();
                break;
        }
    }
    
    function ajaxLogout()
    {
        $p = new Unavaiable;
        $p->setType('terminate')
          ->request();

        RPC::call('movim_redirect', Route::urlize('disconnect')); 
        RPC::commit();
    }
    
    function preparePresence()
    {
        $txt = getPresences();
        $txts = getPresencesTxt();
    
        $session = \Sessionx::start();
        
        $pd = new \Modl\PresenceDAO();
        $p = $pd->getPresence($this->user->getLogin(), $session->ressource);

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
