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

class Roster extends WidgetBase
{
    private $grouphtml;

    function WidgetLoad()
    {
        $this->addcss('roster.css');
        $this->addjs('roster.js');
        $this->registerEvent('roster', 'onRoster');
        $this->registerEvent('rosterupdateditem', 'onRoster');
        $this->registerEvent('contactadd', 'onRoster');
        $this->registerEvent('contactremove', 'onRoster');
        $this->registerEvent('presence', 'onPresence');
    }

    function display()
    {
        $this->view->assign('offline_shown',  '');
        $offline_state = Cache::c('offlineshown');

        $bool = Cache::c('rostershow');
        if($bool)
            $this->view->assign('roster_show', 'hide');
        else
            $this->view->assign('roster_show', '');

        if($offline_state == true)
            $this->view->assign('offline_shown',  'offlineshown');

        $this->view->assign('toggle_cache', $this->genCallAjax('ajaxToggleCache', "'offlineshown'"));
        $this->view->assign('search_contact', $this->genCallAjax('ajaxSearchContact','this.value'));
        
        $this->view->assign('rosterlist', $this->prepareRoster());
    }

    function onPresence($presence)
    {
        $arr = $presence->getPresence();

        $cd = new \modl\ContactDAO();
        $c = $cd->getRosterItem($arr['jid'], true);

        if($c != null) {
            $html = $this->prepareContact($c, $this->getCaps());

            if($c[0]->groupname == null)
                $group = t('Ungrouped');
            else
                $group = $c[0]->groupname;

            RPC::call(
            'movim_delete', 
            $arr['jid'], 
            $html /* this second parameter is just to bypass the RPC filter)*/);

            RPC::call('movim_append', 'group'.$group, $html);
            RPC::call('sortRoster');
        }
    }

    function onRoster($jid)
    {
        $html = $this->prepareRoster();
        RPC::call('movim_fill', 'rosterlist', $html);
        RPC::call('sortRoster');
    }

    /**
     * @brief Force the roster refresh
     * @returns 
     */
    function ajaxRefreshRoster()
    {
        $r = new moxl\RosterGetList();
        $r->request();
    }

    /**
     * @brief Generate the HTML for a roster contact
     * @param $contact 
     * @returns 
     */
    function prepareContact($contact, $caps)
    {
        $arr = array();
        $jid = false;

        // The global presence
        $presence = false;
        $name     = false;

        $presencestxt = getPresencesTxt();
        
        foreach($contact as $c) {
            // We add some basic information
            $arr[$c->ressource]             = $c->toArray();
            $arr[$c->ressource]['avatar']   = $c->getPhoto('s');
            $arr[$c->ressource]['name']     = $c->getTrueName();

            // Some data relative to the presence
            if($c->last != null && $c->last > 60)
                $arr[$c->ressource]['inactive'] = 'inactive';
            else
                $arr[$c->ressource]['inactive'] = '';

            if($c->value && $c->value < 5) {
                $arr[$c->ressource]['presencetxt'] = $presencestxt[$c->value];
            } elseif($c->value == 6)
                $arr[$c->ressource]['presencetxt'] = 'server_error';
            else
                $arr[$c->ressource]['presencetxt'] = 'offline';

            if($presence == false) {
                $presence = $arr[$c->ressource]['presencetxt'];
                $name     = strtolower($arr[$c->ressource]['name']);
            }

            // An action to open the chat widget
            $arr[$c->ressource]['openchat']
                = $this->genCallWidget("Chat","ajaxOpenTalk", "'".$c->jid."'");

            $jid = $c->jid;

            $arr[$c->ressource]['type']   = '';
            $arr[$c->ressource]['client'] = '';
            $arr[$c->ressource]['jingle'] = false;

            // About the entity capability
            if($caps && isset($caps[$c->node.'#'.$c->ver])) {
                $cap  = $caps[$c->node.'#'.$c->ver];
                $arr[$c->ressource]['type'] = $cap->type;
                
                $client = $cap->name;
                $client = explode(' ',$client);
                $arr[$c->ressource]['client'] = strtolower(reset($client));

                // Jingle support
                $features = $cap->features;
                $features = unserialize($features);
                if(array_search('urn:xmpp:jingle:1', $features) !== null
                && array_search('urn:xmpp:jingle:apps:rtp:audio', $features) !== null
                && array_search('urn:xmpp:jingle:apps:rtp:video', $features) !== null
                && (  array_search('urn:xmpp:jingle:transports:ice-udp:0', $features)
                   || array_search('urn:xmpp:jingle:transports:ice-udp:1', $features))
                ){
                    $arr[$c->ressource]['jingle'] = true;
                }
            }

            // Tune
            $arr[$c->ressource]['tune'] = false;
            
            if(($c->tuneartist != null && $c->tuneartist != '') ||
               ($c->tunetitle  != null && $c->tunetitle  != ''))
                $arr[$c->ressource]['tune'] = true;;
        }

        $contactview = $this->tpl();
        $contactview->assign('jid',           $jid);
        $contactview->assign('name',          $name);
        $contactview->assign('presence',      $presence);
        $contactview->assign('contact',       $arr);

        return $contactview->draw('_roster_contact', true);
    }

    /**
     * @brief Generate the HTML for a roster contact
     * @param $contact 
     * @param $inner 
     * @returns 
     */
    /*
    function prepareRosterElement($contact, $caps = false)
    {
        $type = '';
        $jingle = $jingle_audio = $jingle_video = $jingle_ice = false;
        
        if($caps && isset($caps[$contact->node.'#'.$contact->ver])) {
            $cap  = $caps[$contact->node.'#'.$contact->ver];
            $type = $cap->type;
            $client = $cap->name;
            $client = explode(' ',$client);
            $client = reset($client);
            $features = $cap->features;

            $features = unserialize($features);

            if(array_search('urn:xmpp:jingle:1', $features) !== null) {
                $jingle = true;

                if(array_search('urn:xmpp:jingle:apps:rtp:audio', $features) !== null) {
                    $jingle_audio = true;
                }
                if(array_search('urn:xmpp:jingle:apps:rtp:video', $features) !== null) {
                    $jingle_video = true;
                }
                if(array_search('urn:xmpp:jingle:transports:ice-udp:0', $features)
                || array_search('urn:xmpp:jingle:transports:ice-udp:1', $features)) {
                    $jingle_ice = true;
                }
            }
        }
        
        $html = '';
        $html .= '<li
                class="';
                    if(isset($_GET['f']) && $contact->jid == $_GET['f'])
                        $html .= 'active ';
                        
                    if($contact->last != null && $contact->last > 60)
                        $html .= 'inactive ';

                    if($contact->value && $contact->value < 5) {
                        $presencestxt = getPresencesTxt();
                        $html.= $presencestxt[$contact->value];

                        if(isset($client))
                            $html .= ' client '.strtolower($client);
                    } elseif($contact->value == 6)
                        $html .= 'server_error';
                    else
                        $html .= 'offline';

        $html .= '"';

        $html .= '
                id="roster'.$contact->jid.$contact->ressource.'"
                data-jid="'.$contact->jid.'"
                data-priority="'.$contact->value.'"
             >';

        $html .= '<div class="chat on" onclick="'.$this->genCallWidget("Chat","ajaxOpenTalk", "'".$contact->jid."'").'"></div>';

        if($type == 'handheld')
            $html .= '<div class="infoicon mobile"></div>';
            
        if($type == 'web')
            $html .= '<div class="infoicon web"></div>';

        if($type == 'bot')
            $html .= '<div class="infoicon bot"></div>';

        if($jingle_video && $jingle_ice && $jingle_audio)
            $html .= '<div class="infoicon jingle" onclick="Popup.open(\''.$contact->jid.'/'.$contact->ressource.'\')"></div>';

        if(($contact->tuneartist != null && $contact->tuneartist != '') ||
           ($contact->tunetitle != null && $contact->tunetitle != ''))
            $html .= '<div class="infoicon tune"></div>';
        
        $html .= '<a
                    title="'.$contact->jid;
                    if($contact->status != '')
                        $html .= ' - '.htmlentities($contact->status, ENT_QUOTES, 'UTF-8');
                    if($contact->ressource != '')
                        $html .= ' ('.$contact->ressource.')';

        $html .= '"';
        $html .= ' href="'.Route::urlize('friend', $contact->jid).'"
                 >
                <img
                    class="avatar"
                    src="'.$contact->getPhoto('s').'"
                    />'.
                    $contact->getTrueName();
            $html .= '<br /><span class="ressource">';
                    if($contact->status != '')
                        $html .= htmlentities($contact->status, ENT_QUOTES, 'UTF-8') . ' - ';
                        if($contact->rosterask == 'subscribe')
                            $html .= " #";
                        if($contact->ressource != '')
                            $html .= ' '.$contact->ressource.'';
            $html .= '</span>
                 </a>';
        
        $html .= '</li>';

        return $html;
    }
    */
    /**
     * @brief Create the HTML for a roster group and add the title
     * @param $contacts 
     * @param $i 
     * @returns html
     */
    /*
    private function prepareRosterGroup($contacts, &$i, $caps)
    {
        $j = $i;
        // We get the current name of the group
        $currentgroup = $contacts[$i]->groupname;

        // We grab all the contacts of the group 
        $grouphtml = '';
        while(isset($contacts[$i]) && $contacts[$i]->groupname == $currentgroup) {              
            $grouphtml .= $this->prepareRosterElement($contacts[$i], $caps);
            $i++;
        } 
        
        // And we add the title at the head of the group 
        if($currentgroup == '')
            $currentgroup = t('Ungrouped');
            
        $groupshown = '';
        // get the current showing state of the group and the offline contacts
        $groupState = Cache::c('group'.$currentgroup);

        if($groupState == false)
            $groupshown = 'groupshown';
            
        $count = $i-$j;
        
        $grouphtml = '
            <div id="group'.$currentgroup.'" class="'.$groupshown.'">
                <h1 onclick="'.$this->genCallAjax('ajaxToggleCache', "'group".$currentgroup."'").'">'.
                    $currentgroup.' - '.$count.'
                </h1>'.$grouphtml.'
            </div>';
        
        return $grouphtml;
    }*/

    private function getCaps() {
        $capsdao = new modl\CapsDAO();
        $caps = $capsdao->getAll();

        $capsarr = array();
        foreach($caps as $c) {
            $capsarr[$c->node] = $c;
        }

        return $capsarr;
    }

    /**
     * @brief Here we generate the roster
     * @returns 
     * 
     * 
     */
    function prepareRoster()
    {
        
        $contactdao = new \modl\ContactDAO();
        $contacts = $contactdao->getRoster();

        $capsarr = $this->getCaps();

        $roster = array();

        $presencestxt = getPresencesTxt();

        $currentjid     = false;
        $currentarr     = array();

        foreach($contacts as $c) {
            if($c->groupname == '')
                $c->groupname = t('Ungrouped');
            
            if(!isset($roster[$c->groupname])) {
                $roster[$c->groupname] = new stdClass;
                $roster[$c->groupname]->contacts = array();
                $roster[$c->groupname]->html = '';

                $roster[$c->groupname]->name = $c->groupname;

                $roster[$c->groupname]->shown = '';
                // get the current showing state of the group and the offline contacts
                $state = Cache::c('group'.$c->groupname);

                if($state == false)
                    $roster[$c->groupname]->shown = 'groupshown';
                else
                    $roster[$c->groupname]->shown = '';

                $roster[$c->groupname]->toggle =
                    $this->genCallAjax('ajaxToggleCache', "'group".$c->groupname."'");
            }

            if($c->jid == $currentjid) {
                array_push($currentarr, $c);
                $currenthtml = $this->prepareContact($currentarr, $capsarr);
            } else {
                $currentarr = array();
                $currenthtml = '';
                array_push($currentarr, $c);
                $currenthtml = $this->prepareContact($currentarr, $capsarr);
                $roster[$c->groupname]->html .= $currenthtml;
            }

            $currentjid   = $c->jid;
        }

        $listview = $this->tpl();
        $listview->assign('refresh',      $this->genCallAjax('ajaxRefreshRoster'));
        $listview->assign('roster',       $roster);

        return $listview->draw('_roster_list', true);
    }

    /**
     * @brief Toggling boolean variables in the Cache
     * @param $param
     * @returns 
     */
    function ajaxToggleCache($param){
        //$bool = !currentValue
        $bool = (Cache::c($param) == true) ? false : true;
        //toggling value in cache
        Cache::c($param, $bool);
        //$offline = new value of wether offline are shown or not
        $offline = Cache::c('offlineshown');
        
        if($param == 'offlineshown') {
            if($bool)
                Notification::appendNotification(t('Show disconnected contacts'), 'success');
            else
                Notification::appendNotification(t('Hide disconnected contacts'), 'success');
            RPC::call('showRoster', $bool);
        } else {
            if($bool)
                Notification::appendNotification(t('Hide group %s',substr($param, 5)), 'success');
            else
                Notification::appendNotification(t('Show group %s',substr($param, 5)), 'success');
            RPC::call('rosterToggleGroup', $param, $bool, $offline);

        }
        
        RPC::call('focusContact');
        RPC::commit();
    }
    
    /**
     * @brief Show/Hide the Roster
     */
    function ajaxShowHideRoster() {
        $bool = (Cache::c('rostershow') == true) ? false : true;
        Cache::c('rostershow', $bool);
        RPC::call('showHideRoster', $bool);
        RPC::commit();
    }
    
    /**
     *  @brief Search for a contact to add
     */
    function ajaxSearchContact($jid) {
        if(filter_var($jid, FILTER_VALIDATE_EMAIL)) {
            RPC::call('movim_redirect', Route::urlize('friend', $jid));
            RPC::commit();
        } else 
            Notification::appendNotification(t('Please enter a valid Jabber ID'), 'info');
    }
}

?>
