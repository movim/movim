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

    private static $status;

    function WidgetLoad()
    {
        $this->addcss('profile.css');
        $this->addjs('profile.js');
        $this->registerEvent('myvcard', 'onMyVcardReceived');
        $this->registerEvent('mypresence', 'onMyVcardReceived');
    }
    
    function onMyVcardReceived($vcard = false)
    {
		$html = $this->prepareVcard($vcard);
        RPC::call('movim_fill', 'profile', RPC::cdata($html));
    }
    
	function ajaxSetStatus($statustext, $status)
	{
		$xmpp = Jabber::getInstance();
		$xmpp->setStatus($statustext, $status);
	}
    
    function prepareVcard($vcard = false)
    {
        $txt = array(
                1 => t('Online'),
                2 => t('Away'),
                3 => t('Do Not Disturb'),
                4 => t('Long Absence'),
            );
    
        global $sdb;
        $user = new User();
        $me = $sdb->select('Contact', array('key' => $user->getLogin(), 'jid' => $user->getLogin()));
        
		$xmpp = Jabber::getInstance();
        $presence = PresenceHandler::getPresence($user->getLogin(), true, $xmpp->getResource());
        
        if(isset($me[0])) {
            $me = $me[0];
            $html = '<h1>'.$me->getTrueName().'</h1>';
            $html .= '<img src="data:'.$me->getData('phototype').';base64,'.$me->getData('photobin').'">';
            $html .= '<input type="text" id="status" value="'.$presence['status'].'">';
            $html .= '
                <a 
                    onclick="showPresence(this)"
                    class="presence_button button tiny icon '.$presence['presence_txt'].'" 
                    href="#">&nbsp;'.$txt[$presence['presence']].'
                </a>
                <a 
                    onclick="'.$this->genCallAjax('ajaxSetStatus', "':D'", "'online'").'" 
                    style="display: none;";
                    class="presence_button button merged left tiny icon online" href="#">
                </a>
                <a 
                    onclick="'.$this->genCallAjax('ajaxSetStatus', "':D'", "'away'").'"
                    style="display: none;";
                    class="presence_button button merged tiny icon away" href="#">
                </a>
                <a 
                    onclick="'.$this->genCallAjax('ajaxSetStatus', "':D'", "'dnd'").'" 
                    style="display: none;";
                    class="presence_button button merged tiny icon dnd" href="#">
                </a>
                <a 
                    onclick="'.$this->genCallAjax('ajaxSetStatus', "':D'", "'xa'").'" 
                    style="display: none;";
                    class="presence_button button merged right tiny icon xa" href="#">
                </a>';
        }
        
        return $html;
    }
    
    function build()
    {
    ?>
    
        <div id="profile">
			<?php 
				echo $this->prepareVcard();
			?>
        </div>
    <?php
    }
/*    	$this->addcss('profile.css');
    	$this->addjs('profile.js');
		$this->registerEvent('myvcardreceived', 'onMyVcardReceived');
		$this->registerEvent('incomemypresence', 'onMyPresence');

		$this->status = array(
                        1 => array('chat', t('Chat')),
                        2 => array('dnd', t('Do not disturb')),
                        3 => array('away', t('Away')),
                        5 => array('xa', t('Away for a long time')),
                    );
    }

    function onMyVcardReceived($vcard)
    {
		$html = $this->prepareVcard($vcard);
        RPC::call('movim_fill', 'avatar', RPC::cdata($html));
    }

    function prepareVcard($vcard) {
        $html = '<div id="profileavatar"><img alt="' . t("Your avatar") . '" src="data:'.
            $vcard['vCardPhotoType'] . ';base64,' . $vcard['vCardPhotoBinVal'] . '" /></div>'

            .'<h2>'.$vcard['vCardFN'].'<br />'.$vcard['vCardFamily'].'</h2>';
        return $html;
    }

	function ajaxRefreshMyVcard()
	{
		$xmpp = Jabber::getInstance();
		$xmpp->getVCard($jid); // We send the vCard request
	}

	function ajaxPresence($presence)
	{
		$xmpp = Jabber::getInstance();
		$xmpp->setStatus($presence, false);
	}

	function ajaxSetStatus($statustext, $status)
	{
		$xmpp = Jabber::getInstance();
		$xmpp->setStatus($statustext, $status);
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



        // We set the status
        $status = (isset($presences[$user->getLogin()]['status']))
            ? $presences[$user->getLogin()]['status']
            : $user->getLogin();
        ?>
		<div id="profile">
			<div id="presencebutton" onclick="showPresence(this);">
			    <img
			        id="presenceimage"
			        class="<?php echo $this->status[$mypresence][0]; ?>"
			        src="<?php echo $this->respath('img/'.$this->status[$mypresence][0].'.png'); ?>"
			     >
			     <?php echo $this->status[$mypresence][1]; ?>
			</div>

			<ul id="presencelist">
			    <?php foreach($this->status as $key) { ?>
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
    }*/
}

?>
