<?php

/**
 * @file Friendinfos.php
 * This file is part of MOVIM.
 * 
 * @brief A widget which display all the infos of a contact
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

class Friendinfos extends Widget
{
    function WidgetLoad()
    {
		$this->registerEvent('vcardreceived', 'onVcardReceived');
    	$this->addcss('friendinfos.css');
    }
    
    function onVcardReceived($vcard)
    {
		$html = $this->prepareInfos($vcard);
        MovimRPC::call('movim_fill', 'infos', MovimRPC::cdata($html));
    }
    
    function prepareInfos($vcard) {
		$cleanurl = array("http://", "https://");
    
        $html = '<img alt="' . t("Your avatar") . '" src="data:'.
            $vcard['vCardPhotoType'] . ';base64,' . $vcard['vCardPhotoBinVal'] . '" />'
            .'<ul id="infosbox">'
		        .'<li><span>'.t('Firstname') . '</span>' .$vcard['vCardFN'].'</li>'
		        .'<li><span>'.t('Family name') . '</span>' .$vcard['vCardFamily'].'</li>'
		        .'<li><span>'.t('Nickname') . '</span>' .$vcard['vCardNickname'].'</li>'
		        .'<li><span>'.t('Name given') . '</span>' .$vcard['vCardNGiven'].'</li>'
		        .'<li><span>'.t('Website') . '</span><a href="'.$vcard['vCardUrl'].'">' .str_replace($cleanurl, "", $vcard['vCardUrl']).'</a></li>'
		    .'</ul><br /><br />'
		    .'<h3>'.t('About me').'</h3>'
		    .'<div id="description">'.$vcard['vCardDesc'].'</div><br />';
        return $html;
    }
    
	function ajaxRefreshVcard($jid)
	{
		$user = new User();
		$xmpp = XMPPConnect::getInstance($user->getLogin());
		$xmpp->getVCard($jid); // We send the vCard request
	} 
    
    function build()
    {
        ?>
		<div id="friendinfos">
			<div class="config_button" onclick="<?php $this->callAjax('ajaxRefreshVcard', "'".$_GET['f']."'");?>"></div>
			<h3><?php echo t('Contact informations'); ?></h3>
			<div id="infos">
				<?php 
					if(isset($_GET['f']))
						echo $this->prepareInfos(Cache::c('vcard'.$_GET['f']));
					
				?>
			</div>
		</div>
        <?php
    }
}
