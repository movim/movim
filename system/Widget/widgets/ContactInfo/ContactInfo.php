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
        $query = Contact::query()
                            ->join('Presence',
                                              array('Contact.jid' =>
                                                    'Presence.jid'))
                           ->where(array(
                                   'Contact`.`jid' => $_GET['f']));
        $user = Contact::run_query($query);
        
        $html = '';
        
        if(isset($user) && isset($user[0][1])) {
            $contact = $user[0][0];
            
            $presence = $user[0][1]->getPresence();
            
            // Mood
            if($contact->mood->getval() != '') {
                $mood = '';
                foreach(unserialize($contact->mood->getval()) as $m)
                    $mood .= ucfirst(t($m)).',';
                $html .= t("I'm ").substr($mood, 0, -1).'<br />';
            }
            
            // Last seen
            if($user[0][1]->delay->getval() != '0000-00-00 00:00:00' && $this->testIsSet($user[0][1]->delay->getval())) {
                $html .= '<h2>'.t('Last seen').'</h2>';
                $html .= '<span></span>'.date('j M Y - H:i',strtotime($user[0][1]->delay->getval())).'<br />';
            }
            
            // Location
            if(($contact->loclatitude->getval() != '' && 
                $contact->loclongitude->getval() != '') || $contact->getPlace() != ''
              ) {
                $html .= '
                    <h2>'.t('Location').'</h2>';
                    
                $html .= prepareDate(strtotime($contact->loctimestamp->getval())).'<br /><br />';
                if($contact->getPlace() != '')
                    $html .= $contact->getPlace().'<br /><br />';
                
                if($contact->loclatitude->getval() != '' && 
                   $contact->loclongitude->getval() != '')
                $html .= '
                  <div id="mapdiv" style="width: auto; height: 250px;"></div>
                  <script>
                    map = new OpenLayers.Map("mapdiv");
                    map.addLayer(new OpenLayers.Layer.OSM());
                 
                    var lonLat = new OpenLayers.LonLat( '.$contact->loclongitude->getval().' ,'.$contact->loclatitude->getval().' )
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
            if($presence['node'] != '' && $presence['ver'] != '') {
                $clienttype = 
                    array(
                        'bot' => t('Bot'),
                        'pc' => t('Desktop'),
                        'phone' => t('Phone')
                        );
                
                
                $c = new CapsHandler();
                $caps = $c->get($presence['node'].'#'.$presence['ver']);
                
                if($this->testIsSet($caps->getData('type'))) {
                    if($caps->getData('type') == 'phone')
                        $cinfos = '<span class="mobile"></span>';
                }
                if($this->testIsSet($caps->getData('name')))
                    $cinfos .=  $caps->getData('name').'<br />';
                if($cinfos != "")
                    $html .='<h2>'.t('Client Informations').'</h2>' . $cinfos;
            }
            
            if($contact->jid->getval() != $this->user->getLogin()) {
            
                $presences = getPresences();
                
                $html .='<h2>'.t('Actions').'</h2>';
                
                if(isset($presence['presence']) && !in_array($presence['presence'], array(5, 6))) {
                    $html .= '
                        <a
                            class="button tiny icon chat"
                            href="#"
                            style="float: left;"
                            id="friendchat"
                            onclick="'.$this->genCallWidget("Chat","ajaxOpenTalk", "'".$contact->getData('jid')."'").'"
                        >
                            '.$presences[$presence['presence']].' - '.t('Chat').'
                        </a>';
                }
            }
            
        }
                            
        $html .= '<div style="clear: both;"></div>';
        
        $query = RosterLink::query()
                   ->where(array(
                           'key' => $this->user->getLogin(),
                           'jid' => $_GET['f']));
        $r = RosterLink::run_query($query);
        
        if(isset($r[0]->jid) && $r[0]->jid->getval() != '') {
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
            if(!isset($presence['presence']) || $presence['presence'] == 5)
                $html .=' left';
            $html .= '"
                href="#"
                id="friendremoveyes"
                style="float: left; display: none;"
                onclick="'.$this->genCallAjax("ajaxRemoveContact", "'".$_GET['f']."'")
                . 'this.className=\'button tiny icon loading merged left\'; setTimeout(function() {location.reload(true)}, 2000);"
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
                onclick="'.$this->genCallWidget("Notifs","ajaxAddContact", "'".$_GET['f']."'", "''").'"
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
