<?php

/**
 * @package Widgets
 *
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

class Profile extends WidgetBase
{
    
    function WidgetLoad()
    {
    	$this->addcss('profile.css');
    	$this->addjs('profile.js');
		$this->registerEvent('myvcardreceived', 'onMyVcardReceived');
		$this->registerEvent('incomemypresence', 'onMyPresence');
    }

    function onMyVcardReceived($vcard)
    {
		$html = $this->prepareVcard($vcard);
        RPC::call('movim_fill', 'avatar', RPC::cdata($html));
    }
    
    function prepareVcard($vcard) {
        $html = '<div id="profileavatar"><img alt="' . t("Your avatar") . '" src="data:'.
            $vcard['vCardPhotoType'] . ';base64,' . $vcard['vCardPhotoBinVal'] . '" /></div>'
            
            .'<h2>'.$vcard['vCardFN'].'<br />'.$vcard['vCardFamily'].'</h2>'
#            .'<div id="desc">'.$vcard['vCardDesc'].'</div>'
            ;
        return $html;
    }

	function ajaxRefreshMyVcard()
	{
		$user = new User();
		$xmpp = Jabber::getInstance($user->getLogin());
		$xmpp->getVCard($jid); // We send the vCard request
	}  
	
	function ajaxPresence($presence)
	{
		$user = new User();
		$xmpp = Jabber::getInstance($user->getLogin());
		$xmpp->setStatus(false, $presence);
	}
	
	function ajaxSetStatus($status, $show = false)
	{
		$user = new User();
		$xmpp = Jabber::getInstance($user->getLogin());
		$xmpp->setStatus($status, $show);
	}
	
	function onMyPresence($presence)
	{
	    $uri = $this->respath();
        RPC::call('movim_fill', 'presencebutton', RPC::cdata(
            '<img id="presenceimage" class="'.$presence['show'].'" src="'.str_replace('jajax.php', '',$uri).'img/'.$presence['show'].'.png">'
        ));
	}

    function build()
    {
    
        // We grab the presences
        $session = Session::start(APP_NAME);
        $presences = $session->get('presences');
        
        // We grab my presence
        $user = new User();
		$xmpp = Jabber::getInstance($user->getLogin());		
		$mypresence = $presences[$user->getLogin()][$xmpp->getResource()];
        
        $array = array(
                    1 => array('chat', t('Chat')),
                    2 => array('dnd', t('Do not disturb')),
                    3 => array('away', t('Away')),
                    5 => array('xa', t('Away for a long time')),
                );
        
        // We set the status
        $status = (isset($presences[$user->getLogin()]['status'])) 
            ? $presences[$user->getLogin()]['status'] 
            : $user->getLogin();
        ?>
		<div id="profile">
			<div id="presencebutton" onclick="showPresence(this);">
			    <img id="presenceimage" class="<?php echo $array[$mypresence][0]; ?>" src="<?php echo $this->respath('img/'.$array[$mypresence][0].'.png'); ?>"><?php echo $array[$mypresence][1]; ?>
			</div>
			
			<ul id="presencelist">
			    <?php foreach($array as $key) { ?>
			        <li onclick="<?php $this->callAjax('ajaxSetStatus', "getStatusText()", "'$key[0]'");?> closePresence();">
			            <img src="<?php echo $this->respath('img/'.$key[0].'.png'); ?>">
			            <?php echo $key[1]; ?>
			         </li>
			    <?php } ?>
			</ul>
		
			<div class="config_button" onclick="<?php $this->callAjax('ajaxRefreshMyVcard');?>"></div>
			<?php echo $this->prepareVcard(Cache::c('myvcard')); ?>
				
			
			<div id="profiledescription">
			    <p>
			        <input 
				        type="text" 
				        id="profilestatustext" 
				        value="<?php echo $status; ?>" 
				        onkeypress="if(event.keyCode == 13) {<?php $this->callAjax('ajaxSetStatus', "getStatusText()", "getStatusShow()");?>}"
			        />
			    </p>
			</div>
		</div>
        <?php
    }
}

?>
