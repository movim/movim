<?php

/**
 * @package Widgets
 *
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

class Friendvcard extends WidgetBase
{
    function WidgetLoad()
    {
		$this->registerEvent('vcardreceived', 'onVcardReceived');
    	$this->addcss('friendvcard.css');
    }
    
    function onVcardReceived($vcard)
    {
		$html = $this->prepareInfos($vcard);
        RPC::call('movim_fill', 'friendvcard', RPC::cdata($html));
    }
    
    private function displayIf($element, $title, $html = false) {
        if(!$html) $html = $element;
        if(isset($element) && $element != '')
                return '<div class="element"><span>'.$title.'</span><div class="content">'.$html.'</div></div>';
    }
    
    function prepareInfos($vcard) {
        $html ='
        <form><br />
            <fieldset>
                <legend>'.t('General Informations').'</legend>';
        
        $html .= $this->displayIf($vcard["vCardFN"], t('Name'));
        $html .= $this->displayIf($vcard["vCardNickname"], t('Nickname'));
        $html .= $this->displayIf($vcard["from"], t('Adress'));
        $html .= $this->displayIf($vcard["vCardBDay"], t('Date of Birth'), date('j F Y',strtotime($vcard["vCardBDay"])));
        
        $html .= '<br />';
        
        $html .= $this->displayIf($vcard["vCardUrl"], t('Website'), '<a href="'.$vcard["vCardUrl"].'">'.$vcard["vCardUrl"].'</a>');
        $html .= $this->displayIf($vcard["vCardPhotoType"], t('Avatar'), '<img src="data:'.$vcard["vCardPhotoType"].';base64,'.$vcard["vCardPhotoBinVal"].'">');
        
        $html .= '<br />';
        $html .= $this->displayIf($vcard["vCardDesc"], t('About Me'));
        
        $html .= '
            </fieldset>
        </form>';

        return $html;
    }
    
	function ajaxRefreshVcard($jid)
	{
		$user = new User();
		$xmpp = Jabber::getInstance($user->getLogin());
		$xmpp->getVCard($jid); // We send the vCard request
	}
	
    function ajaxRemoveContact($jid) {
		$xmpp = Jabber::getInstance();
        $xmpp->removeContact($jid);
    } 
    
    function build()
    {
        ?>
		<div class="tabelem" title="<?php echo t('Profile'); ?>" id="friendvcard">
					<div class="config_button" onclick="<?php $this->callAjax('ajaxRefreshVcard', "'".$_GET['f']."'");?>"></div>
				<?php 
				    //global $sdb;
				    //$objs = $sdb->select('Contact', array('name' => 'etenil'));
				    //print_r(Cache::c('vcard'.$_GET['f']));
					if(isset($_GET['f']))
						echo $this->prepareInfos(Cache::c('vcard'.$_GET['f']));
					
				?>
                
		</div>
        <?php
    }
}
