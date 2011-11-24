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

class ContactCard extends WidgetBase
{

    function WidgetLoad()
    {
    	$this->addcss('contactcard.css');
		$this->registerEvent('vcard', 'onVcard');
    }
    
    private function displayIf($element, $title, $html = false) {
        if(!$html) $html = $element;
        if(isset($element) && $element != '')
                return '<div class="element"><span>'.$title.'</span><div class="content">'.$html.'</div></div>';
    }
    
    function onVcard($contact)
    {
        $html = $this->prepareContactCard($contact);
        RPC::call('movim_fill', 'contactcard', RPC::cdata($html));
    }
    
    function prepareContactCard($contact)
    {
        $presence = PresenceHandler::getPresence($contact->getData('jid'), true);

        $html .='
        <a 
	        class="button tiny icon rm';
	    if(isset($presence['presence']) && $presence['presence'] != 5)
	        $html .=' merged right';
	    $html .= '" 
	        href="#"
	        style="float: right;"
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
	        class="button tiny icon no merged right" 
	        href="#"
	        style="float: right; display: none;"
	        id="friendremoveno" 
	        onclick="
	            document.querySelector(\'#friendremoveask\').style.display = \'block\';
	            document.querySelector(\'#friendremoveyes\').style.display = \'none\';
	            this.style.display = \'none\'
	        "
	    >
	        '.t('No').'
	    </a>
	    
	    <a 
	        class="button tiny icon yes merged';
	    if(!isset($presence['presence']) || $presence['presence'] == 5)
	        $html .=' left'; 
	    $html .= '"
	        href="#" 
	        id="friendremoveyes" 
	        style="float: right; display: none;"
	        onclick="'.$this->genCallAjax("ajaxRemoveContact", "'".$contact->getData('jid')."'").'"
	    >
	        '.t('Yes').'
	    </a>';
	    
        if(isset($presence['presence']) && $presence['presence'] != 5) {
            $html .= '
                <a 
	                class="button tiny icon chat merged left" 
	                href="#"
	                style="float: right;"
	                id="friendchat"  
	                onclick="'.$this->genCallWidget("Chat","ajaxOpenTalk", "'".$contact->getData('jid')."'").'"
	            >
	                '.t('Chat').'
	            </a>';
        }
	    
	    
        $html .='
        <form><br />
            <fieldset class="protect red">
                <legend>'.t('General Informations').'</legend>';
        
        $html .= $this->displayIf($contact->getData('fn'), t('Name'));
        $html .= $this->displayIf($contact->getData('name'), t('Nickname'));
        $html .= $this->displayIf($contact->getData('jid'), t('Adress'));
        if($contact->getData('date') != '0000-00-00')
        $html .= $this->displayIf($contact->getData('date'), t('Date of Birth'), date('j F Y',strtotime($contact->getData('date'))));
        
        $html .= '<br />';
        
        $html .= $this->displayIf($contact->getData('url'), t('Website'), '<a target="_blank" href="'.$contact->getData('url').'">'.$contact->getData('url').'</a>');
        $html .= $this->displayIf($contact->getPhoto(), t('Avatar'), '<img src="'.$contact->getPhoto().'">');
        
        $html .= '<br />';
        //$html .= $this->displayIf($vcard["vCardDesc"], t('About Me'));
        
        $html .= '
            </fieldset>
        </form>';
        return $html;
    }
    
    function ajaxRemoveContact($jid) {
		$xmpp = Jabber::getInstance();
        $xmpp->removeContact($jid);
        
        global $sdb;
    	$user = new User();
        $contact = $sdb->select('Contact', array('key' => $user->getLogin(), 'jid' => $jid));
        $sdb->delete($contact[0]);
    }
    
    function build()
    {
        global $sdb;
        $user = new User();
        $contact = $sdb->select('Contact', array('key' => $user->getLogin(), 'jid' => $_GET['f']));
    ?>
    <div class="tabelem" title="<?php echo t('Profile'); ?>" id="contactcard">
        <?php
        if(isset($contact[0]))
            echo $this->prepareContactCard($contact[0]);
        ?>
    </div>
    <?php
    }
}
