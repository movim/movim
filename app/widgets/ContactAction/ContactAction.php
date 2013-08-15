<?php

/**
 * @package Widgets
 *
 * @file ContactAction.php
 * This file is part of MOVIM.
 *
 * @brief Do some actions on a contact
 *
 * @author Jaussoin Timothée <edhelas@gmail.com>
 *
 * Copyright (C)2013 MOVIM project
 *
 * See COPYING for licensing information.
 */

class ContactAction extends WidgetCommon
{
    /**
     * @brief Adding a new contact 
     * @param $jid 
     * @param $alias 
     * @returns 
     */
    function ajaxAddContact($jid) {
        $r = new moxl\RosterAddItem();
        $r->setTo($jid)
          ->setFrom($this->user->getLogin())
          ->request();
    }
    
    function ajaxSubscribeContact($jid) {
        $p = new moxl\PresenceSubscribe();
        $p->setTo($jid)
          ->request();
    }
    
    
    function ajaxRemoveContact($jid) {         
        $r = new moxl\RosterRemoveItem();
        $r->setTo($jid)
          ->request();
    }
    
    function ajaxUnsubscribeContact($jid) {         
        $p = new moxl\PresenceUnsubscribe();
        $p->setTo($jid)
          ->request();
    }
    
    function prepareContactInfo()
    {
        $cd = new \modl\ContactDAO();
        $c = $cd->getRosterItem($_GET['f']);
        
        $html = '';
        
        if(isset($c)) {            
            // Chat button
            if($c->jid != $this->user->getLogin()) {
            
                $presences = getPresences();
                
                $html .='<h2>'.t('Actions').'</h2>';
                
                $ptoc = array(
                    1 => 'green',
                    2 => 'yellow',
                    3 => 'red', 
                    4 => 'purple'
                        );
                
                if(isset($c->presence) && !in_array($c->presence, array(5, 6))) {
                    $html .= '
                        <a
                            class="button color '.$ptoc[$c->presence].' icon chat"
                            style="float: left;"
                            id="friendchat"
                            onclick="'.$this->genCallWidget("Chat","ajaxOpenTalk", "'".$c->jid."'").'"
                        >
                            '.$presences[$c->presence].' - '.t('Chat').'
                        </a>';
                }
            }
            
            $html .= '<div style="clear: both;"></div>';
            
            $html .='
            <a
                class="button icon rm black"
                style="margin: 1em 0px; display: block;"
                id="friendremoveask"
                onclick="
                    document.querySelector(\'#friendremoveyes\').style.display = \'block\';
                    document.querySelector(\'#friendremoveno\').style.display = \'block\';
                    this.style.display = \'none\'
                "
            >
                '.t('Remove this contact').'
            </a>

            <a
                class="button color green icon yes merged left';
            if(!isset($c->presence) || $c->presence == 5)
                $html .=' left';
            $html .= '"
                id="friendremoveyes"
                style="margin: 1em 0px; float: left; display: none;"
                onclick="
                    setTimeout(function() {'.
                        $this->genCallAjax("ajaxRemoveContact", "'".$_GET['f']."'").
                    '}, 1500);'.
                    $this->genCallAjax("ajaxUnsubscribeContact", "'".$_GET['f']."'").
                'this.className=\'button color green icon loading merged left\'; setTimeout(function() {location.reload(false)}, 2000);"
            >
                '.t('Yes').'
            </a>

            <a
                class="button color red icon no merged right"
                style="margin: 1em 0px; float: left; display: none;"
                id="friendremoveno"
                onclick="
                    document.querySelector(\'#friendremoveask\').style.display = \'block\';
                    document.querySelector(\'#friendremoveyes\').style.display = \'none\';
                    this.style.display = \'none\'
                "
            >
                '.t('No').'
            </a>';
        } elseif($_GET['f'] != $this->user->getLogin()) {
                            
            $html .='<h2>'.t('Actions').'</h2>';
            
            $html .='
            <a
                class="button color purple icon add"
                onclick="
                    setTimeout(function() {'.
                        $this->genCallAjax("ajaxAddContact", "'".$_GET['f']."'").
                    '}, 1500);'.
                $this->genCallAjax("ajaxSubscribeContact", "'".$_GET['f']."'").
                'this.className=\'button color purple icon loading merged left\'; setTimeout(function() {location.reload(false)}, 3000);"
            >
                '.t('Invite this user').'
            </a>';
        }
        
        return $html;
    }
    
    function build() {
        echo $this->prepareContactInfo();
    }
}
