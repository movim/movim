<?php

/**
 * @package Widgets
 *
 * @file Roster.php
 * This file is part of MOVIM.
 *
 * @brief The Roster widget
 *
 * @author Jaussoin Timothée <edhelas@gmail.com>
 *
 * @version 1.0
 * @date 30 August 2010
 *
 * Copyright (C)2010 MOVIM project
 *
 * See COPYING for licensing information.
 */

class ContactInfo extends WidgetCommon
{
    function WidgetLoad() {
        
    }
    
    function ajaxRemoveContact($jid) {         
        $r = new moxl\RosterRemoveItem();
        $r->setTo($jid)
          ->request();
        
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
            // Mood
            if($c->mood) {
                $moodarray = getMood();
                
                $html .= '<h2>'.t('Mood').'</h2>';
                $mood = '';
                foreach(unserialize($c->mood) as $m)
                    $mood .= $moodarray[$m].',';
                $html .= t("I'm ").substr($mood, 0, -1).'<br />';
            }
            
            if($c->tuneartist || $c->tunetitle) {
                $html .= '<h2>'.t('Listening').'</h2>';
                if($c->tuneartist)
                    $artist = $c->tuneartist. ' - ';
                if($c->tunetitle)
                    $title = $c->tunetitle;
                if($c->tunesource)
                    $album = t('on').' '.$c->tunesource;
                $html .= $artist.$title.' '.$album;
            }
            
            // Last seen
            if($c->delay && $c->delay != '0000-00-00 00:00:00') {
                $html .= '<h2>'.t('Last seen').'</h2>';
                $html .= prepareDate(strtotime($c->delay)).'<br />';
            }
            
            if($c->loclatitude != '' && $c->loclongitude != ''
             || $c->getPlace() != '') {
                 
                $html .= '
                    <h2>'.t('Location').'</h2>';
                    
                $html .= prepareDate(strtotime($c->loctimestamp)).'<br /><br />';
                if($c->getPlace() != '')
                    $html .= $c->getPlace().'<br /><br />';
                
                if(isset($c->loclatitude) && isset($c->loclongitude))                  
                    $html .= '
                        <div style="height: 250px; margin: 0em -1.2em;" id="map"></div>
                        <script type="text/javascript">
                                var map = L.map("map").setView(['.$c->loclatitude.' ,'.$c->loclongitude.'], 11);
                                
                                L.tileLayer("http://tile.openstreetmap.org/{z}/{x}/{y}.png", {
                                    attribution: "",
                                    maxZoom: 18
                                }).addTo(map);
                                var marker = L.marker(['.$c->loclatitude.' ,'.$c->loclongitude.']).addTo(map)
                        </script>';

            }
            

			
            
            // Client informations
            if($c->node && $c->ver) {                
                $node = $c->node.'#'.$c->ver;

                $cad = new \modl\CapsDAO();
                $caps = $cad->get($node);

                $clienttype = 
                    array(
                        'bot' => t('Bot'),
                        'pc' => t('Desktop'),
                        'phone' => t('Phone'),
                        'handheld' => t('Phone'),
                        'web' => t('Web'),
                        );
                        
                if(isset($caps) && $caps->name != '' && $caps->type != '' ) {
                    $cinfos = '';
                    $cinfos .=  $caps->name.' ('.$clienttype[$caps->type].')<br />';
                    
                    $html .='<h2>'.t('Client Informations').'</h2>' . $cinfos;
                }
            }
            
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
                onclick="'.$this->genCallAjax("ajaxRemoveContact", "'".$_GET['f']."'")
                . 'this.className=\'button color green icon loading merged left\'; setTimeout(function() {location.reload(false)}, 2000);"
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
                onclick="'.$this->genCallWidget("Roster","ajaxAddContact", "'".$_GET['f']."'", "''")
                . 'this.className=\'button color purple icon loading merged left\'; setTimeout(function() {location.reload(false)}, 2000);"
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
