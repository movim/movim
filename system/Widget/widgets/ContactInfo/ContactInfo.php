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
            
            if($c->tuneartist) {
                $html .= '<h2>'.t('Listening').'</h2>';
                $html .= $c->tuneartist. ' - '.$c->tunetitle.' '.t('on').' '.$c->tunesource;
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
                  <div id="mapdiv" style="width: auto; height: 250px;"></div>
                  <script>
                    map = new OpenLayers.Map("mapdiv");
                    map.addLayer(new OpenLayers.Layer.OSM());
                 
                    var lonLat = new OpenLayers.LonLat( '.$c->loclongitude.' ,'.$c->loclatitude.' )
                          .transform(
                            new OpenLayers.Projection("EPSG:4326"), // transform from WGS 1984
                            map.getProjectionObject() // to Spherical Mercator Projection
                          );
                 
                    var zoom=11;
                 
                    var markers = new OpenLayers.Layer.Markers( "Markers" );
                    map.addLayer(markers);
                 
                    markers.addMarker(new OpenLayers.Marker(lonLat));
                 
                    map.setCenter (lonLat, zoom);
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
                
                if(isset($c->presence) && !in_array($c->presence, array(5, 6))) {
                    $html .= '
                        <a
                            class="button tiny icon chat"
                            href="#"
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
                class=""
                href="#"
                style="margin: 10px 0px; display: block;"
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
                class="button tiny icon yes merged left';
            if(!isset($c->presence) || $c->presence == 5)
                $html .=' left';
            $html .= '"
                href="#"
                id="friendremoveyes"
                style="float: left; display: none;"
                onclick="'.$this->genCallAjax("ajaxRemoveContact", "'".$_GET['f']."'")
                . 'this.className=\'button tiny icon loading merged left\'; setTimeout(function() {location.reload(false)}, 2000);"
            >
                '.t('Yes').'
            </a>

            <a
                class="button tiny icon no merged right"
                href="#"
                style="float: left; display: none;"
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
            $html .='
            <a
                class="button tiny icon add"
                href="#"
                onclick="'.$this->genCallWidget("Roster","ajaxAddContact", "'".$_GET['f']."'", "''")
                . 'this.className=\'button tiny icon loading merged left\'; setTimeout(function() {location.reload(false)}, 2000);"
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
