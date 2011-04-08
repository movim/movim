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
		$html = $this->prepareVcard($vcard);
        MovimRPC::call('movim_fill', 'avatar', MovimRPC::cdata($html));
    }
    
    function prepareVcard($vcard) {
        $html = '<img alt="' . t("Your avatar") . '" style="width: 60px;" src="data:'.
            $vcard['vCardPhotoType'] . ';base64,' . $vcard['vCardPhotoBinVal'] . '" />'
            
            .'<div id="infos">'.$vcard['vCardNickname'].'<br />'.$vcard['vCardFN'].'</div>'
            .'<div id="persmess">'.$vcard['vCardDesc'].'</div>'
            ;
        return $html;
    }

	function ajaxRefreshVcard($jid = false)
	{
		$user = new User();
		$xmpp = XMPPConnect::getInstance($user->getLogin());
		$xmpp->getVCard($jid); // We send the vCard request
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
			<div class="config_button" onclick="<?php $this->callAjax('ajaxRefreshVcard', "'".$_GET['f']."'");?>"></div>
			<div id="avatar">
				<?php 
					echo $this->prepareVcard(Cache::handle('vcard'));
				?>
			</div>
		</div>
        <?php
    }
}

?>
