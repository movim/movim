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
		$this->xmpp->getVCard($jid);
	}
    
    private function testIsSet($element)
    {
        if(isset($element) && $element != '')
            return true;
        else
            return false;
    }

    function ajaxRemoveContact($jid) {
        $this->xmpp->removeContact($jid);

        global $sdb;
        $contact = $sdb->select('Contact', array('key' => $this->user->getLogin(), 'jid' => $jid));
        $sdb->delete($contact[0]);
    }
	
	function prepareContactSummary($contact)
	{
        $gender = getGender();
        $marital = getMarital();
        
        $presence = PresenceHandler::getPresence($contact->getData('jid'), true);
        $html ='<h1>'.$contact->getTrueName().'</h1><center><img src="'.$contact->getPhoto().'"/></center>';
        
        if($contact->getData('vcardreceived') != 1)
            $html .= '<script type="text/javascript">setTimeout(\''.$this->genCallAjax('ajaxRefreshVcard', '"'.$contact->getData('jid').'"').'\', 500);</script>';
            
        if($presence != NULL)
            $html .= '<div id="status">'.$presence['status'].'</div>';
            
        $html .='<h2>'.t('General Informations').'</h2>';
        
        if($contact->getData('gender') != 'N' && $this->testIsSet($contact->getData('gender')))
            $html .= '<span class="'.$contact->getData('gender').'"></span>';
            
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
            
            
        if($this->testIsSet($contact->getData('desc')))
            $html .= '
                <h2>'.t('About Me').'</h2>
                <div style="
                    overflow-y: auto;
                    overflow-x: hidden;
                    display: block; 
                    max-height: 200px;
                    word-wrap: break-word;
                    text-align: justify;
                    white-space: normal;">'.
                    prepareString($contact->getData('desc')).'
                </div>';
        
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
	        onclick="'.$this->genCallAjax("ajaxRemoveContact", "'".$contact->getData('jid')."'").'"
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

        return $html;
	}
    
    function build()
    {
        global $sdb;
        $contact = $sdb->select('Contact', array('key' => $this->user->getLogin(), 'jid' => $_GET['f']));
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
