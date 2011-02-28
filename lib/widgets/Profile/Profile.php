<?php

/**
 * @file Profile.php
 * This file is part of MOVIM.
 * 
 * @brief The Profile widget
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

class Profile extends Widget
{
    
    function WidgetLoad()
    {
    	$this->addcss('profile.css');
    	$this->addjs('profile.js');
		$this->registerEvent('vcardreceived', 'onVcardReceived');
    }

    function onVcardReceived($vcard)
    {
        $html = '<img alt="' . t("Your avatar") . '" style="width: 60px;" src="data:'.
            $vcard['vCardPhotoType'] . ';base64,' . $vcard['vCardPhotoBinVal'] . '" />'
            
            .'<div id="infos">'.$vcard['vCardNickname'].'<br />'.$vcard['vCardFN'].'</div>';
            
        MovimRPC::call('movim_fill', 'avatar', MovimRPC::cdata($html));
    }

	function ajaxRefreshVcard()
	{
		$user = new User();
		$xmpp = XMPPConnect::getInstance($user->getLogin());
		$xmpp->getVCard(); // We send the vCard request
	}  
	
	function ajaxPresence($presence)
	{
		$user = new User();
		$xmpp = XMPPConnect::getInstance($user->getLogin());
		$xmpp->setStatus(false, $presence);
	}

    function build()
    {
        ?>
		<div id="profile">
			<div id="avatar">
				<div style="width: 60px; height: 45px; background-color: #9BBBC6;"></div>
				<div id="infos"><br /></div>
            </div>
            <!--<select name="presence" id="presence" onchange="<?php $this->callAjax('ajaxPresence', "this.value"); ?>">
				<option value="chat"><?php echo t('Online');?></option>
				<option value="away"><?php echo t('Away');?></option>
				<option value="dnd"><?php echo t('Do Not Disturb');?></option>
			</select>-->
			<input type="button"
			onclick="<?php $this->callAjax('ajaxRefreshVcard');?>"
			value="<?php echo t('Refresh vCard'); ?>" />
		</div>
        <?php
    }
}

?>
