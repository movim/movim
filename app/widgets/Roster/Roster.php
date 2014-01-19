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
    }

    function onPresence($presence)
    {
        $arr = $presence->getPresence();

        $cd = new \modl\ContactDAO();
        $c = $cd->getRosterItem($arr['jid'], true);

        $caps = $this->getCaps();
        
        if($c != null) {
            foreach($c as $item) {
                $html = $this->prepareRosterElement($item, $caps);
                
                RPC::call(
                'movim_delete', 
                'roster'.$item->jid.$item->ressource, 
                $html /* this second parameter is just to bypass the RPC filter*/);

                if($item->groupname == null)
                    $group = t('Ungrouped');
                else
                    $group = $item->groupname;

                RPC::call('movim_append', 'group'.$group, $html);
            }

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
     * @param $inner 
     * @returns 
     */
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
    
    /**
     * @brief Create the HTML for a roster group and add the title
     * @param $contacts 
     * @param $i 
     * @returns html
     * 
     * 
     */
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
    }

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
        $html = '';
        
        $rd = new \modl\RosterLinkDAO();
        
        $capsarr = $this->getCaps();
        
        if(count($contacts) > 0) {
            $i = 0;
            
            while($i < count($contacts))
                $html .= $this->prepareRosterGroup($contacts, $i, $capsarr);

        } else {
            $html .= '<script type="text/javascript">setTimeout(\''.$this->genCallAjax('ajaxRefreshRoster').'\', 1500);</script>';
            $html .= '
                <span class="nocontacts">'.
                    t('No contacts ? You can add one using the %s button bellow or going to the %sExplore page%s',
                    '+', 
                    '<br /><a class="button color green icon users" href="'.Route::urlize('explore').'">', '</a>').'
                </span>';
        }
        /*
        $roster = array();

        foreach($contacts as $c) {
            if(!isset($roster[$c->groupname])) {
                $roster[$c->groupname] = array();
            }
            
            if(!isset($roster[$c->groupname][$c->jid])) {
                $roster[$c->groupname][$c->jid] = $c->toArray();
            } else {
                array_push($roster[$c->groupname][$c->jid], $c->toArray());
            }
        }

        //var_dump($roster);
        $html = json_encode($roster);
        */
        return $html;
    }

    /**
     * @brief Toggling boolean variables in the Cache
     * @param $param
     * @returns 
     * 
     * 
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
