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

class Roster extends WidgetBase
{
    private $grouphtml;

    function load()
    {
        $this->addcss('roster.css');
        $this->addjs('angular.js');
        $this->addjs('roster.js');
        $this->registerEvent('roster_getlist_handle', 'onRoster');
        /*$this->registerEvent('roster_additem_handle', 'onUpdate');
        $this->registerEvent('roster_removeitem_handle', 'onDelete');
        $this->registerEvent('roster_updateitem_handle', 'onUpdate');
        $this->registerEvent('presence', 'onUpdate');*/
    }

    function display()
    {

    }

    function onDelete($packet)
    {
        $jid = $packet->content;
        if($jid != null){
            RPC::call('deleteContact', $jid);
        }
    }

    function onUpdate($packet)
    {
        $contacts = $packet->content;
        if($contacts != null){
            foreach($contacts as &$c) {
                if($c->groupname == '')
                    $c->groupname = $this->__('roster.ungrouped');
                
                $ac = $c->toArray();
                $this->prepareContact($ac, $c, $this->getCaps());
                $c = $ac;
            }
            RPC::call('updateContact', json_encode($contacts));
        }
    }

    function onRoster()
    {
        $results = $this->prepareRoster();

        RPC::call('initContacts', $results['contacts']);
        RPC::call('initGroups', $results['groups']);
    }

    /**
     * @brief Force the roster refresh
     * @returns
     */
    function ajaxGetRoster()
    {
        $this->onRoster();
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
     *  @brief Search for a contact to add
     */
    function ajaxSearchContact($jid) {
        if(filter_var($jid, FILTER_VALIDATE_EMAIL)) {
            RPC::call('movim_redirect', Route::urlize('friend', $jid));
            RPC::commit();
        } else 
            Notification::appendNotification($this->__('roster.jid_error'), 'info');
    }

    /**
     * @brief Get data from to database to pass it on to angular in JSON
     * @param
     * @returns $result: a json for the contacts and one for the groups
     */
    function prepareRoster(){
        //Contacts
        $contactdao = new \Modl\ContactDAO();
        $contacts = $contactdao->getRoster();
        
        $capsarr = $this->getCaps();

        $result = array();
        
        if(isset($contacts)) {
            foreach($contacts as &$c) {
                if($c->groupname == '')
                    $c->groupname = $this->__('roster.ungrouped');
                
                $ac = $c->toArray();
                $this->prepareContact($ac, $c, $capsarr);
                $c = $ac;
            }
        }
        $result['contacts'] = json_encode($contacts);
        
        //Groups
        $rd = new \Modl\RosterLinkDAO();
        $groups = $rd->getGroups();
        if(!in_array("Ungrouped", $groups)) $groups[] = "Ungrouped";
        movim_log($groups);
        $groups = array_flip($groups);
        $result['groups'] = json_encode($groups);
        
        return $result;
    }

    /**
     * @brief Get data for contacts display in roster
     * @param   &$c: the contact as an array and by reference,
     *          $oc: the contact as an object,
     *          $caps: an array of capabilities
     * @returns
     */
    function prepareContact(&$c, $oc, $caps){
        $arr = array();
        $jid = false;

        $presencestxt = getPresencesTxt();
        
        // We add some basic information
        $c['rosterview']   = array();
        $c['rosterview']['avatar']   = $oc->getPhoto('s');
        $c['rosterview']['name']     = $oc->getTrueName();
        $c['rosterview']['friendpage']     = $this->route('friend', $oc->jid);

        // Some data relative to the presence
        if($oc->last != null && $oc->last > 60)
            $c['rosterview']['inactive'] = 'inactive';
        else
            $c['rosterview']['inactive'] = '';

        if($oc->value && $oc->value != 5){
            if($oc->value && $oc->value == 6) {
                $c['rosterview']['presencetxt'] = 'server_error';
            } else {
                $c['rosterview']['presencetxt'] = $presencestxt[$oc->value];
            }
            $c['value'] = intval($c['value']);
        } else {
            $c['rosterview']['presencetxt'] = 'offline';
            $c['value'] = 5;
        }

        // An action to open the chat widget
        $c['rosterview']['openchat']
            = $this->genCallWidget("Chat","ajaxOpenTalk", "'".$oc->jid."'");

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
        
        if(($oc->tuneartist != null && $oc->tuneartist != '') 
            || ($oc->tunetitle  != null && $oc->tunetitle  != ''))
            $c['rosterview']['tune'] = true;
    }

}


?>
