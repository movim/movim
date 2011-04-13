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
		$this->registerEvent('myvcardreceived', 'onMyVcardReceived');
    }

    function onMyVcardReceived($vcard)
    {
		$html = $this->prepareVcard($vcard);
        MovimRPC::call('movim_fill', 'avatar', MovimRPC::cdata($html));
    }
    
    function prepareVcard($vcard) {
        $html = '<img alt="' . t("Your avatar") . '" style="width: 60px;" src="data:'.
            $vcard['vCardPhotoType'] . ';base64,' . $vcard['vCardPhotoBinVal'] . '" />'
            
            .'<div id="myinfos">'.$vcard['vCardFN'].'<br />'.$vcard['vCardFamily'].'</div>'
            .'<div id="desc">'.$vcard['vCardDesc'].'</div>'
            ;
        return $html;
    }

	function ajaxRefreshMyVcard()
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
	
	function ajaxSetStatus($status)
	{
		$user = new User();
		$xmpp = XMPPConnect::getInstance($user->getLogin());
		$xmpp->setStatus($status, false);
	}

    function build()
    {
        ?>
		<div id="profile">
			<div class="config_button" onclick="<?php $this->callAjax('ajaxRefreshMyVcard');?>"></div>
			
			<div id="avatar">
				<?php 
					echo $this->prepareVcard(Cache::c('myvcard'));
				?>
			</div>
			<input 
				type="text" 
				id="statusText" 
				value="<?php echo t('Status'); ?>" 
				onfocus="myFocus(this);" 
				onblur="myBlur(this);" 
				onkeypress="if(event.keyCode == 13) {<?php $this->callAjax('ajaxSetStatus', "getStatusText()");?>}"
			/>
		</div>
        <?php
    }
}

?>
