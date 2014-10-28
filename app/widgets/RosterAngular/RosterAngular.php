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

use Moxl\Xec\Action\Roster\GetList;

class RosterAngular extends WidgetBase
{
    private $grouphtml;

    function load()
    {
        $this->addcss('roster.css');
        $this->addjs('angular.js');
        $this->addjs('angular-filters.js');
        $this->addjs('roster.js');
        $this->registerEvent('roster', 'onRoster');
        $this->registerEvent('rosterupdateditem', 'onRoster');
        $this->registerEvent('contactadd', 'onRoster');
        $this->registerEvent('contactremove', 'onRoster');
        /*$this->registerEvent('presence', 'onPresence');*/
    }

    function display()
    {
        /*$this->view->assign('offline_shown',  '');
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
        
        $this->view->assign('rosterlist', $this->prepareRoster());*/
    }

    function onPresence($packet)
    {
        $c = $packet->content;

        if($c != null) {
            $html = $this->prepareContact($c, $this->getCaps());

            if($c[0]->groupname == null)
                $group = t('Ungrouped');
            else
                $group = $c[0]->groupname;

            RPC::call(
            'movim_delete', 
            $c[0]->jid, 
            $html /* this second parameter is just to bypass the RPC filter */);

            RPC::call('movim_append', 'group'.$group, $html);
            RPC::call('sortRoster');
        }
    }

    function onRoster($jid)
    {
        //$html = $this->prepareRoster();
        $results = $this->prepareRosterAngular();
        //RPC::call('movim_fill', 'rosterlist', $html);
        //RPC::call('sortRoster');
        RPC::call('getContacts', $results['contacts']);
        RPC::call('getGroups', $results['groups']);
    }

    /**
     * @brief Force the roster refresh
     * @returns 
     */
    function ajaxRefreshRoster()
    {
        $r = new GetList;
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
                $arr[$c->ressource]['client'] = strtolower(preg_replace('/[^a-zA-Z0-9_ \-()\/%-&]/s', '', reset($client)));

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

    private function getCaps() {
        $capsdao = new \Modl\CapsDAO();
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
     */
    function prepareRoster()
    {
        
        $contactdao = new \Modl\ContactDAO();
        $contacts = $contactdao->getRoster();
        $capsarr = $this->getCaps();

        $roster = array();

        $currentjid     = false;
        $currentarr     = array();

        if(isset($contacts)) {
            foreach($contacts as $c) {
                if($c->groupname == '')
                    $c->groupname = $this->__('roster.ungrouped');
                
                if(!isset($roster[$c->groupname])) {
                    // create new group in roster
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

                if($c->jid != $currentjid && !empty($samejid)) {
                    $currenthtml = $this->prepareContact($samejid, $capsarr);
                    $roster[$c->groupname]->html .= $currenthtml;
                    
                    $samejid = array();
                }
                array_push($samejid, $c);

                $currentjid   = $c->jid;
            }
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
    /*function ajaxToggleCache($param){
        //$bool = !currentValue
        $bool = (Cache::c($param) == true) ? false : true;
        //toggling value in cache
        Cache::c($param, $bool);
        //$offline = new value of wether offline are shown or not
        $offline = Cache::c('offlineshown');
        
        if($param == 'offlineshown') {
            if($bool)
                Notification::appendNotification($this->__('roster.show_disconnected'), 'success');
            else
                Notification::appendNotification($this->__('roster.hide_disconnected'), 'success');
            RPC::call('showRoster', $bool);
        } else {
            if($bool)
                Notification::appendNotification($this->__('roster.hide_group',substr($param, 5)), 'success');
            else
                Notification::appendNotification($this->__('roster.show_group',substr($param, 5)), 'success');
            RPC::call('rosterToggleGroup', $param, $bool, $offline);

        }
        
        RPC::call('focusContact');
        RPC::commit();
    }*/
    
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
            Notification::appendNotification($this->__('roster.jid_error'), 'info');
    }
    
    

/*=========*/
    function prepareRosterAngular(){
        //Contacts
        $contactdao = new \Modl\ContactDAO();
        $contacts = $contactdao->getRoster();
        
        $capsarr = $this->getCaps();
        
        if(isset($contacts)) {
            foreach($contacts as &$c) {
                if($c->groupname == '')
                    $c->groupname = $this->__('roster.ungrouped');
                
                $ac = $c->toArray();
                $this->prepareContactAngular($ac, $c, $capsarr);
                $c = $ac;
            }
        }
        $result['contacts'] = json_encode($contacts);
        
        //Groups
        $rd = new \Modl\RosterLinkDAO();
        $groups = $rd->getGroups();
        if(!in_array("ungrouped"))$groups[] = "ungrouped";
        $groups = array_flip($groups);
        $result['groups'] = json_encode($groups);
        
        return $result;
    }
    
    function prepareContactAngular(&$c, $oc, $caps){
        $arr = array();
        $jid = false;

        // The global presence
        $presence = false;
        $name     = false;

        $presencestxt = getPresencesTxt();
        
            // We add some basic information
            //WUT ?? $c->rosterview             = $c->toArray();
            $c['rosterview']   = array();
            $c['rosterview']['avatar']   = $oc->getPhoto('s');
            $c['rosterview']['name']     = $oc->getTrueName();
            $c['rosterview']['friendpage']     = $this->route('friend', $oc->jid);
            
            //movim_log(serialize($c));

            // Some data relative to the presence
            if($oc->last != null && $oc->last > 60)
                $c['rosterview']['inactive'] = 'inactive';
            else
                $c['rosterview']['inactive'] = '';

            if($c->value && $c->value < 5) {
                $c['rosterview']['presencetxt'] = $presencestxt[$oc->value];
            } elseif($c->value == 6)
                $c['rosterview']['presencetxt'] = 'server_error';
            else
                $c['rosterview']['presencetxt'] = 'offline';

            if($presence == false) {
                $presence = $c['rosterview']['presencetxt'];
                $name     = strtolower($c['rosterview']['name']);
            }

            // An action to open the chat widget
            $c['rosterview']['openchat']
                = $this->genCallWidget("Chat","ajaxOpenTalk", "'".$oc->jid."'");

            /*$jid = $c->jid;*/

            $c['rosterview']['type']   = '';
            $c['rosterview']['client'] = '';
            $c['rosterview']['jingle'] = false;

            // About the entity capability
            if($caps && isset($caps[$oc->node.'#'.$oc->ver])) {
                $cap  = $caps[$oc->node.'#'.$oc->ver];
                $c['rosterview']['type'] = $cap->type;
                
                $client = $cap->name;
                $client = explode(' ',$client);
                $c['rosterview']['client'] = strtolower(preg_replace('/[^a-zA-Z0-9_ \-()\/%-&]/s', '', reset($client)));

                // Jingle support
                $features = $cap->features;
                $features = unserialize($features);
                if(array_search('urn:xmpp:jingle:1', $features) !== null
                && array_search('urn:xmpp:jingle:apps:rtp:audio', $features) !== null
                && array_search('urn:xmpp:jingle:apps:rtp:video', $features) !== null
                && (  array_search('urn:xmpp:jingle:transports:ice-udp:0', $features)
                   || array_search('urn:xmpp:jingle:transports:ice-udp:1', $features))
                ){
                    $c['rosterview']['jingle'] = true;
                }
            }

            // Tune
            $c['rosterview']['tune'] = false;
            
            if(($oc->tuneartist != null && $oc->tuneartist != '') ||
               ($oc->tunetitle  != null && $oc->tunetitle  != ''))
                $c['rosterview']['tune'] = true;;
        

        /*$contactview = $this->tpl();
        $contactview->assign('jid',           $jid);
        $contactview->assign('name',          $name);
        $contactview->assign('presence',      $presence);
        $contactview->assign('contact',       $arr);

        return $contactview->draw('_roster_contact', true);*/
    }

}


?>
