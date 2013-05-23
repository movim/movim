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

        $this->cached = false;
    }

	function onPresence($presence)
	{
	    $arr = $presence->getPresence();
	    RPC::call('incomingPresence', $arr['jid'], $arr['presence_txt']);
	}

    function onRoster()
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
					if($contact->jid == $_GET['f'])
                        $html .= 'active ';
                        
                    if(isset($contact->last) && $contact->last > 60)
                        $html .= 'inactive ';

                    if(isset($contact->presence)) {
                        $presencestxt = getPresencesTxt();
                        $html.= $presencestxt[$contact->presence];
                    } else
                        $html .= 'offline';

        $html .= '"';

        $html .= '
                id="roster'.$contact->jid.'"
             >';
             
        $type = '';
             
        if($caps) {
            foreach($caps as $c) {
                if($c->node == $contact->node.'#'.$contact->ver) {
                    $type = $c->type;
                }
            }
        }

        $html .= '<div class="chat on" onclick="'.$this->genCallWidget("Chat","ajaxOpenTalk", "'".$contact->jid."'").'"></div>';

        if($type == 'handheld')
            $html .= '<div class="infoicon mobile"></div>';
            
        if($type == 'web')
            $html .= '<div class="infoicon web"></div>';

        if($type == 'bot')
            $html .= '<div class="infoicon bot"></div>';
            
        if((isset($contact->tuneartist) && $contact->tuneartist != '') ||
           (isset($contact->tunetitle) && $contact->tunetitle != ''))
            $html .= '<div class="infoicon tune"></div>';
        
        $html .= '<a
					title="'.$contact->jid;
                    if($contact->status != '')
                        $html .= ' - '.htmlentities($contact->status);
                    if($contact->ressource != '')
                        $html .= ' ('.$contact->ressource.')';

        $html .= '"';
        $html .= ' href="?q=friend&f='.$contact->jid.'"
                 >
                <img
                    class="avatar"
                    src="'.$contact->getPhoto('xs').'"
                    />'.
                    $contact->getTrueName();
            $html .= '<span class="ressource">';
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
        $currentgroup = $contacts[$i]->group;

        // Temporary array to prevent duplicate contact
        $duplicate = array();
        
        // We grab all the contacts of the group 
        $grouphtml = '';
        while(isset($contacts[$i]) && $contacts[$i]->group == $currentgroup) {
            //if(!in_array($contacts[$i]->jid, $duplicate)) {                
                $grouphtml .= $this->prepareRosterElement($contacts[$i], $caps);
                array_push($duplicate, $contacts[$i]->jid);
            //}
            $i++;
        } 
        
        // And we add the title at the head of the group 
        if($currentgroup == '')
            $currentgroup = t('Ungrouped');
			
        $groupshown = '';
        // get the current showing state of the group and the offline contacts
		$groupState = Cache::c('group'.$currentgroup);

        if($groupState == true)
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

        if(count($contacts) > 0) {
            $i = 0;
            
            while($i < count($contacts))
                $html .= $this->prepareRosterGroup($contacts, $i, $caps);

        } else {
            $html .= '<script type="text/javascript">setTimeout(\''.$this->genCallAjax('ajaxRefreshRoster').'\', 1500);</script>';
        }

        return $html;
	}
    
    /**
     * @brief Adding a new contact from the Rostermenu
     * @param $jid 
     * @param $alias 
     * @returns 
     * 
     * 
     */
    function ajaxAddContact($jid, $alias) {
        $r = new moxl\RosterAddItem();
        $r->setTo($jid)
          ->request();
          
        $p = new moxl\PresenceSubscribe();
        $p->setTo($jid)
          ->request();
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
            $call = $this->genCallAjax('ajaxToggleChat');
        } else {
            $arrow = 'contract';
            $ptoggle = 'closePopup();';
            $call = '';
        }
        
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
    
	function build()
    {
        $offlineshown = '';
        $offlineState = Cache::c('offlineshown');
        
        $bool = Cache::c('rostershow');
        if($bool)
            $rostershow = 'hide';

        if($offlineState == true)
            $offlineshown = 'offlineshown';        
	?>
        <div id="roster" class="<?php echo $rostershow; ?>">
            <ul id="rosterlist" class="<?php echo $offlineshown; ?>">
            <?php echo $this->prepareRoster(); ?>
            </ul>
            <script type="text/javascript">sortRoster();</script>
        </div>
        
        <div id="rostermenu" class="menubar">
            <ul class="menu">
                <li id="search">
                    <label class="search" for="rostershow"></label>
                    <input type="checkbox" id="rostershow"/>
                    <div class="tabbed">
                        <input 
                            type="text" 
                            name="search" 
                            id="request" 
                           
                            autocomplete="off" 
                            onkeyup="rosterSearch(event);" 
                            onclick="focusContact();" 
                            placeholder="<?php echo t('Search');?>"/>
                    </div>
                </li>
                <li title="<?php echo t('Add'); ?>">
                    <label class="plus" for="addc"></label>
                    <input type="checkbox" id="addc"/>
                    <div class="tabbed">
                        <form id="addcontact">
                            <div class="element large mini">
                                <label for="addjid"><?php echo t('JID'); ?></label>
                                <input 
                                    id="addjid" 
                                    class="tiny" 
                                    placeholder="user@server.tld" 
                                    onfocus="myFocus(this);" 
                                    onblur="myBlur(this);"
                                />
                            </div>
                            <!--<div class="element large mini">
                                <label for="addalias"><?php echo t('Alias'); ?></label>
                                <input 
                                    id="addalias"
                                    type="text"
                                    class="tiny" 
                                    placeholder="<?php echo t('Alias'); ?>" 
                                    onfocus="myFocus(this);" 
                                    onblur="myBlur(this);"
                                />
                            </div>-->
                            <a 
                                class="button tiny icon no merged left black"
                                href="#"
                                id="addrefuse"
                                onclick="cancelAddJid();">
                                <?php echo t('Cancel'); ?>
                            </a><a 
                                class="button tiny icon yes merged right black" 
                                href="#" 
                                id="addvalidate" 
                                onclick="<?php $this->callAjax("ajaxAddContact", "getAddJid()", "getAddAlias()"); ?> cancelAddJid();">
                                <?php echo t('Send request'); ?>
                            </a>
                        </form>
                    </div>
                </li>
                <li 
                    onclick="<?php echo $this->callAjax('ajaxToggleCache', "'offlineshown'");?>"
                    title="<?php echo t('Show/Hide'); ?>">
                    <a class="users" href="#"></a>
                </li>
                <div id="chattoggle">
                    <?php echo $this->prepareChatToggle(); ?>
                </div>
                <li 
                    onclick="<?php echo $this->callAjax('ajaxShowHideRoster');?>"
                    title="<?php echo t('Show/Hide'); ?>">
                    <a class="down" href="#"></a>
                </li>
            </ul>
        </div>
    <?php
    }
}

?>
