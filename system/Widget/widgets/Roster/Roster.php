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
        $this->registerEvent('contactadd', 'onRoster');
        $this->registerEvent('contactremove', 'onRoster');
		$this->registerEvent('presence', 'onPresence');
		$this->registerEvent('vcard', 'onVcard');
        
        $this->cached = false;
    }
    
	function onPresence($presence)
	{
	    $arr = $presence->getPresence();
	    RPC::call('incomingPresence',
                      RPC::cdata($arr['jid']), RPC::cdata($arr['presence_txt']));
	}
	
    function onVcard($contact)
    {
        $query = \Presence::query()->select()
                           ->where(array(
                                   'key' => $this->user->getLogin(),
                                   'jid' => $contact->getData('jid')))
                           ->limit(0, 1);
        $data = \Presence::run_query($query);
        
        $c = array();
        $c[0] = $contact;
        $c[1] = $data[0];
        
        $html = $this->prepareRosterElement($c, true);
        RPC::call('movim_fill', 'roster'.$contact->getData('jid'), RPC::cdata($html));
    }
	
    function onRoster()
    {
		$html = $this->prepareRoster();
        RPC::call('movim_fill', 'rosterlist', RPC::cdata($html));
        RPC::call('sortRoster');
    }
    
	function ajaxRefreshRoster()
	{
        $r = new moxl\RosterGetList();
        $r->request();
	}
	
	function prepareRosterElement($contact, $inner = false)
	{
        if(isset($contact[1]))
            $presence = $contact[1]->getPresence();
        $start = 
            '<li
                class="';
                    if(isset($presence['presence']))
                        $start .= ''.$presence['presence_txt'].' ';
                    else
                        $start .= 'offline ';
                        
                    if($contact[0]->getData('jid') == $_GET['f'])
                        $start .= 'active ';
        $start .= '" 
                id="roster'.$contact[0]->getData('jid').'" 
             >';
             
        $middle = '<div class="chat on" onclick="'.$this->genCallWidget("Chat","ajaxOpenTalk", "'".$contact[0]->getData('jid')."'").'"></div>
                 <a 
					title="'.$contact[0]->getData('jid');
                    if($presence['status'] != '')
                        $middle .= ' - '.$presence['status'];
        $middle .= '"';
        $middle .= ' href="?q=friend&f='.$contact[0]->getData('jid').'"
                 >
                    <img class="avatar"  src="'.$contact[0]->getPhoto('xs').'" />'.
                    '<span>'.$contact[0]->getTrueName();
						if($contact[0]->getData('rosterask') == 'subscribe')
							$middle .= " #";
                        if($presence['ressource'] != '')
                            $middle .= ' ('.$presence['ressource'].')';
            $middle .= '</span>
                 </a>';
        $end = '</li>';

        if($inner == true)
            return $middle;
        else
            return $start.$middle.$end;
	}
    
    function stackGroup() {
        
    }
	
	function prepareRoster()
	{
        $query = Contact::query()->join('Presence',
                                              array('Contact.jid' =>
                                                    'Presence.jid'))
                                 ->where(
                                    array(
                                        'Contact`.`key' => $this->user->getLogin(),
                                        array(
                                            'Contact`.`rostersubscription!' => 'none',
                                            '|Contact`.`rosterask' => 'subscribe')))
                                 ->orderby('Contact.group', true);

        $contacts = Contact::run_query($query);
    
        $html = '';
        $group = '';

        if($contacts != false) {
            
            if($contacts[0][0]->getData('group') == '')
                $html .= '<div><h1>'.t('Ungrouped').'</h1>';
            else {
                $group = $contacts[0][0]->getData('group');
                $html .= '<div><h1>'.$group.'</h1>';
            }
            
            // Temporary array to prevent duplicate contact
            $duplicate = array();
            
            foreach($contacts as $c) {
                if(!in_array($c[0]->getData('jid'), $duplicate)) {
                    if($group != $c[0]->getData('group')) {
                        $group = $c[0]->getData('group');
                        $html .= '</div>';
                        if($group == '')
                            $html .= '<div><h1>'.t('Ungrouped').'</h1>';
                        else
                            $html .= '<div><h1>'.$group.'</h1>';
                    }
                    
                    $html .= $this->prepareRosterElement($c);
                    
                    array_push($duplicate, $c[0]->getData('jid'));
                }
            }
            $html .= '</div>';
        } else {
            $html .= '<script type="text/javascript">setTimeout(\''.$this->genCallAjax('ajaxRefreshRoster').'\', 1500);</script>';
        }

        return $html;
	}
    
    function build()
    { 
    ?>
        <div id="roster">
            <ul id="rosterlist">
            <?php echo $this->prepareRoster(); ?>
            </ul>
            <div id="rostermenu">
                <ul>
                    <li onclick="showRoster(this);"><a href="#"><?php echo t('Show/Hide'); ?></a></li>
                </ul>
            </div>
            <div class="config_button" onclick="<?php $this->callAjax('ajaxRefreshRoster');?>"></div>
            <script type="text/javascript">sortRoster();</script>
        </div>
    <?php
    }
}

?>
