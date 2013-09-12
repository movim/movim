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
            
        if($this->user->getConfig('chatbox') == 1)
            $this->view->assign('chatbox', true);
        else
            $this->view->assign('chatbox', false);
            
        $this->view->assign('toggle_cache', $this->genCallAjax('ajaxToggleCache', "'offlineshown'"));
        //$this->view->assign('show_hide', $this->genCallAjax('ajaxShowHideRoster'));
        $this->view->assign('search_contact', $this->genCallAjax('ajaxSearchContact','this.value'));
    }

    function onPresence($presence)
    {
        $arr = $presence->getPresence();

        $cd = new \modl\ContactDAO();
        $c = $cd->getRosterItem($arr['jid']);
        
        if($c != null) {
            $c->setPresence($presence);
            $html = $this->prepareRosterElement($c);

            if($c->groupname == null)
                $group = t('Ungrouped');
            else
                $group = $c->groupname;

            RPC::call(
                'movim_delete', 
                'roster'.$arr['jid'], 
                $html /* this second parameter is just to bypass the RPC filter*/);
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
     * 
     * 
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
     * 
     * 
     */
    function prepareRosterElement($contact, $caps = false)
    {
        $html = '';
        $html .= '<li
                class="';
                    if(isset($_GET['f']) && $contact->jid == $_GET['f'])
                        $html .= 'active ';
                        
                    if($contact->last != null && $contact->last > 60)
                        $html .= 'inactive ';

                    if($contact->value) {
                        $presencestxt = getPresencesTxt();
                        $html.= $presencestxt[$contact->value];
                    } else
                        $html .= 'offline';

        $html .= '"';

        $html .= '
                id="roster'.$contact->jid.'"
             >';
             
        $type = '';

        if($caps && isset($caps[$contact->node.'#'.$contact->ver])) {
            $type = $caps[$contact->node.'#'.$contact->ver]->type;
        }

        $html .= '<div class="chat on" onclick="'.$this->genCallWidget("Chat","ajaxOpenTalk", "'".$contact->jid."'").'"></div>';

        if($type == 'handheld')
            $html .= '<div class="infoicon mobile"></div>';
            
        if($type == 'web')
            $html .= '<div class="infoicon web"></div>';

        if($type == 'bot')
            $html .= '<div class="infoicon bot"></div>';

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

        // Temporary array to prevent duplicate contact
        $duplicate = array();
        
        // We grab all the contacts of the group 
        $grouphtml = '';
        while(isset($contacts[$i]) && $contacts[$i]->groupname == $currentgroup) {
            if(!in_array($contacts[$i]->jid, $duplicate)) {                
                $grouphtml .= $this->prepareRosterElement($contacts[$i], $caps);
                array_push($duplicate, $contacts[$i]->jid);
            }
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
        
        $capsdao = new modl\CapsDAO();
        $caps = $capsdao->getAll();

        $capsarr = array();
        foreach($caps as $c) {
            $capsarr[$c->node] = $c;
        }     
        
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
            RPC::call('showRoster', $bool);
        } else 
            RPC::call('rosterToggleGroup', $param, $bool, $offline);
        
        RPC::call('focusContact');
        RPC::commit();
    }
    
    function ajaxToggleChat()
    {
        //$bool = !currentValue
        $bool = (Cache::c('chatpop') == true) ? false : true;
        //toggling value in cache
        Cache::c('chatpop', $bool);
        
        RPC::call('movim_fill', 'chattoggle', $this->prepareChatToggle());
        
        RPC::commit();
    }
    
    function prepareChatToggle()
    {
        $chatpop = Cache::c('chatpop');
        
        if($chatpop) {
            $arrow = 'expand';
            $ptoggle = 'openPopup();';
        } else {
            $arrow = 'contract';
            $ptoggle = 'closePopup();';
        }
        
        $call = $this->genCallAjax('ajaxToggleChat');
        
        $html = '
            <li 
                onclick="'.$call.' '.$ptoggle.'"
                title="'.t('Show/Hide').'">
                <a class="'.$arrow.'" href="#"></a>
            </li>';
            
        return $html;
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
