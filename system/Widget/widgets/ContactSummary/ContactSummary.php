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

class ContactSummary extends WidgetBase
{

    function WidgetLoad()
    {
    	$this->addcss('contactsummary.css');
		$this->registerEvent('vcard', 'onVcard');
    }
    
    function onVcard($contact)
    {
        $html = $this->prepareContactSummary($contact);
        RPC::call('movim_fill', 'contactsummary', RPC::cdata($html));
    }
    
	function ajaxRefreshVcard($jid)
	{
        $r = new moxl\VcardGet();
        $r->setTo($jid)->request();
	}
    
    private function testIsSet($element)
    {
        if(isset($element) && $element != '')
            return true;
        else
            return false;
    }

    function ajaxRemoveContact($jid) {
		if(checkJid($jid)) {            
            $r = new moxl\RosterRemoveItem();
            $r->setTo($jid)
              ->request();
            
			$p = new moxl\PresenceUnsubscribe();
            $p->setTo($jid)
              ->request();
		} else {
			throw new MovimException("Incorrect JID `$jid'");
		}

        global $sdb;
        $contact = $sdb->select('Contact', array('key' => $this->user->getLogin(), 'jid' => $jid));
        $sdb->delete($contact[0]);
    }
	
	function prepareContactSummary($contact)
	{
        $gender = getGender();
        $marital = getMarital();
        
        $query = \Presence::query()->select()
                           ->where(array(
                                   'key' => $this->user->getLogin(),
                                   'jid' => $contact->getData('jid')))
                           ->limit(0, 1);
        $data = \Presence::run_query($query);
        
        if(isset($data[0]))
            $presence = $data[0]->getPresence();
        
        $html ='<h1>'.$contact->getTrueName().'</h1><center><img src="'.$contact->getPhoto().'"/></center>';
        
        if($contact->getData('vcardreceived') != 1)
            $html .= '<script type="text/javascript">setTimeout(\''.$this->genCallAjax('ajaxRefreshVcard', '"'.$contact->getData('jid').'"').'\', 2000);</script>';
            
        if($presence != NULL && $presence['status'] != '')
            $html .= '<div class="textbubble">'.$presence['status'].'</div>';
            
            
        // General Informations
                    
        $html .='<h2>'.t('General Informations').'</h2>';
        
        if($contact->getData('gender') != 'N' && $this->testIsSet($contact->getData('gender')))
            $html .= '<span class="'.$contact->getData('gender').'"></span>';
        else
            $html .= '<span></span>';
            
        if($this->testIsSet($contact->getData('name')))
            $html .= $contact->getData('name').'<br />';
        else
            $html .= $contact->getTrueName().'<br />';
            
        if($contact->getData('marital') != 'none' && $this->testIsSet($contact->getData('marital')))
            $html .= '<span class="hearth"></span>'.$marital[$contact->getData('marital')].'<br />';
            
        if($contact->getData('date') != '0000-00-00' && $this->testIsSet($contact->getData('date')))
            $html .= '<span class="birth"></span>'.date('j M Y',strtotime($contact->getData('date'))).'<br />';
            
        if($this->testIsSet($contact->getData('jid')))
            $html .= '<span class="address"></span>'.$contact->getData('jid').'<br />';
            
        if($this->testIsSet($contact->getData('url')))
            $html .= '<span class="website"></span>'.'<a target="_blank" href="'.$contact->getData('url').'">'.$contact->getData('url').'</a>';
        
        if(isset($data[0])) {
            if($data[0]->mood->getval() != '') {
                $mood = '';
                foreach(unserialize($data[0]->mood->getval()) as $m)
                    $mood .= ucfirst(t($m)).',';
                $html .= '<br /><span></span>'.t("I'm ").substr($mood, 0, -1).'<br />';
            }
            
            // Last seen
            if($data[0]->delay->getval() != '0000-00-00 00:00:00' && $this->testIsSet($data[0]->delay->getval())) {
                $html .= '<h2>'.t('Last seen').'</h2>';
                $html .= '<span></span>'.date('j M Y - H:i',strtotime($data[0]->delay->getval())).'<br />';
            }
            
            // Location
            if(($data[0]->loclatitude->getval() != '' && 
                $data[0]->loclongitude->getval() != '') || $data[0]->getPlace() != ''
              ) {
                $html .= '
                    <h2>'.t('Location').'</h2>';
                if($data[0]->getPlace() != '')
                    $html .= $data[0]->getPlace().'<br /><br />';
                $html .= '
                  <div id="mapdiv" style="width: auto; height: 250px;"></div>
                  <script>
                    map = new OpenLayers.Map("mapdiv");
                    map.addLayer(new OpenLayers.Layer.OSM());
                 
                    var lonLat = new OpenLayers.LonLat( '.$data[0]->loclongitude->getval().' ,'.$data[0]->loclatitude->getval().' )
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
        }
            
        // About me
            
        if($this->testIsSet(prepareString($contact->getData('desc')))) {
            $html .= '
                <h2>'.t('About Me').'</h2>
                <div class="textbubble" style="text-align: left; margin-top: 0px;">
                    <span>'.prepareString($contact->getData('desc')).'</span>
                </div>';
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
        
        // Actions
        
        $html .='<h2>'.t('Actions').'</h2>';
        
        $presences = getPresences();
        
        if(isset($presence['presence']) && $presence['presence'] != 5) {
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
        
        $html .= '<div style="clear: both;"></div>';

        if($contact->getData('rostersubscription') != 'none') {
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
                onclick="'.$this->genCallAjax("ajaxRemoveContact", "'".$contact->getData('jid')."'")
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
        } else {
            $html .='<br />
            <a
                class="button tiny icon add"
                href="#"
                onclick="'.$this->genCallWidget("Notifs","ajaxAddContact", "'".$contact->getData('jid')."'", "''").'"
            >
                '.t('Invite this user').'
            </a>';
        }

        return $html;
	}
    
    function build()
    {
        //global $sdb;
        //$contact = $sdb->select('Contact', array('key' => $this->user->getLogin(), 'jid' => $_GET['f']));
        
        $query = \Contact::query()->select()
                                   ->where(array(
                                           'key' => $this->user->getLogin(),
                                           'jid' => $_GET['f']));
        $contact = \Contact::run_query($query);
        ?>
        <div id="contactsummary">
        <?php
        if(isset($contact[0])) {
            echo $this->prepareContactSummary($contact[0]);
        ?>
        <div class="config_button" onclick="<?php $this->callAjax('ajaxRefreshVcard', "'".$contact[0]->getData('jid')."'");?>"></div>
        <?php } 
        
        else {
        ?>
        <script type="text/javascript"><?php $this->callAjax('ajaxRefreshVcard', "'".$_GET['f']."'");?></script>
        <?php } ?>
        </div>
        <?php
    }
}
