<?php

/**
 * @package Widgets
 *
 * @file Friendinfos.php
 * This file is part of MOVIM.
 * 
 * @brief A widget which brief infos of a contact
 *
 * @author Timothée	Jaussoin <edhelas_at_gmail_dot_com>
 *
 * @version 1.0
 * @date 20 October 2010
 *
 * Copyright (C)2010 MOVIM project
 * 
 * See COPYING for licensing information.
 */

class Friendinfos extends WidgetBase
{
    function WidgetLoad()
    {
		$this->registerEvent('vcardreceived', 'onVcardReceived');
    	$this->addcss('friendinfos.css');
    }
    
    /**
     * If we receive a vCard
     *
     * @param array $vcard
     * @return void
     */
    function onVcardReceived($vcard)
    {
		$html = $this->prepareInfos($vcard);
        RPC::call('movim_fill', 'friendinfos', RPC::cdata($html));
    }
    
    /**
     * Prepare the informations
     *
     * @param unknown $vcard
     * @return void
     */
    function prepareInfos($vcard) {
        $c = new ContactHandler();
        $contact = $c->get($_GET['f']);
        
		$html = '<div id="friendavatar">';
            if($vcard != false) {
                //$html .= '<img alt="' . t("Your avatar") . '" src="data:'.
                //    $vcard['vCardPhotoType'] . ';base64,' . $vcard['vCardPhotoBinVal'] . '" />';
                $html .= '<img alt="' . t("Your avatar") . '" src="'.$contact->getPhoto().'" />';
            }
        $html .= '</div>';
        
        /*$name = $vcard['vCardFN'].' '.$vcard['vCardFamily'];
        
        if($name == " ")
            $name = $vcard['vCardNickname'];
        if($name == "")
            $name = $vcard['vCardNGiven'];
        if($name == "")
            $name = $vcard['from'];*/
        $name = $contact->getTrueName();
            
        $html .= '<h2 title="'.$vcard['from'].'">'.$name.'</h2>';
        
        $val = array(
            'vCardUrl' => t('Website'),
            //'vCardDesc' => t('About me'),
            'vCardBDay' => t('Date of birth')
        );    
        
        $html .= '<ul id="infosbox">';
        if($vcard != false) {
            foreach($vcard as $key => $value) {
                if(array_key_exists($key, $val) && $value != '')
                    $html .= '<li><span>'.$val[$key] . '</span>' .$value.'</li>';
            }
        } else {
            //$html .= '<div onclick="'.$this->genCallAjax('ajaxRefreshVcard', "'".$_GET['f']."'").'" class="refresh">'.t('Refresh the data').'</div>';
            $html .= '<script type="text/javascript">'.$this->genCallAjax('ajaxRefreshVcard', "'".$_GET['f']."'").'</script>';
        }
        $html .= '</ul>';
        
        $session = Session::start(APP_NAME);
        $presences = $session->get('presences');
        
	    $status = ($presences[$vcard['from']]['status'] != "") 
	        ? $presences[$vcard['from']]['status'] 
	        : t('Hye, I\'m on Movim !');
        
            $html .= '<div id="frienddescription"><p>'.$status.'</p></div>';
        
        return $html;
    }
    
    /**
	 * Ask to refresh the vCard
	 *
	 * @param string $jid
	 * @return void
	 */
	function ajaxRefreshVcard($jid)
	{
		$user = new User();
		$xmpp = Jabber::getInstance($user->getLogin());
		$xmpp->getVCard($jid); // We send the vCard request
	}
	
	/**
     * Ask to remove the contact
     *
     * @param unknown $jid
     * @return void
     */
    function ajaxRemoveContact($jid) {
		$xmpp = Jabber::getInstance();
        $xmpp->removeContact($jid);
    } 
    
    function build()
    {
        ?>
		<div id="friendinfos">
		    <a 
		        class="button tiny" 
		        href="#" 
		        id="friendremove" 
		        onclick="<?php $this->callAjax("ajaxRemoveContact", "'".$_GET['f']."'"); ?>"
		    >
		        <?php echo t('Remove this contact'); ?>
		    </a>
			<div 
			    class="config_button" 
			    onclick="<?php $this->callAjax('ajaxRefreshVcard', "'".$_GET['f']."'");?>"
			></div>
			<?php 
			    /*global $sdb;
		    	$contact = new Contact();
		    	$xmpp = Jabber::getInstance();
	            $sdb->load($contact, array('key' => $xmpp->getCleanJid(), 'jid' => $_GET['f']));*/
				if(isset($_GET['f']))
					echo $this->prepareInfos(Cache::c('vcard'.$_GET['f']));
				
			?>
                
		</div>
        <?php
    }
}
