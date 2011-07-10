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

class Friendinfos extends WidgetBase
{
    function WidgetLoad()
    {
		$this->registerEvent('vcardreceived', 'onVcardReceived');
    	$this->addcss('friendinfos.css');
    }
    
    function onVcardReceived($vcard)
    {
		$html = $this->prepareInfos($vcard);
        RPC::call('movim_fill', 'friendinfos', RPC::cdata($html));
    }
    
    function prepareInfos($vcard) {
    		
		$html = '<div id="friendavatar">';
            if($vcard != false) {
                $html .= '<img alt="' . t("Your avatar") . '" src="data:'.
                    $vcard['vCardPhotoType'] . ';base64,' . $vcard['vCardPhotoBinVal'] . '" />';
            }
        $html .= '</div>';
        
        // coucou les gehs c'est kro bien comme truc!! isous doux a toi petit ahge
            
        $name = $vcard['vCardFN'].' '.$vcard['vCardFamily'];
        if($name == " ")
            $name = $vcard['vCardNickname'];
        if($name == "")
            $name = $vcard['vCardNGiven'];
        if($name == "")
            $name = $vcard['from'];
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
            $html .= '<div onclick="'.$this->genCallAjax('ajaxRefreshVcard', "'".$_GET['f']."'").'" class="refresh">'.t('Refresh the data').'</div>';
        }
        $html .= '</ul>';
        
        $session = Session::start(APP_NAME);
        $presences = $session->get('presences');
        
	    $status = ($presences[$vcard['from']]['status'] != "") 
	        ? $presences[$vcard['from']]['status'] 
	        : t('Hye, I\'m on Movim !');
        
            $html .= '<div id="frienddescription"><p>'.$status.'</p></div>';
        
        

        /*$html = '<img alt="' . t("Your avatar") . '" src="data:'.
            $vcard['vCardPhotoType'] . ';base64,' . $vcard['vCardPhotoBinVal'] . '" />'
            .'<ul id="infosbox">'
		        .'<li><span>'.t('Firstname') . '</span>' .$vcard['vCardFN'].'</li>'
		        .'<li><span>'.t('Family name') . '</span>' .$vcard['vCardFamily'].'</li>'
		        .'<li><span>'.t('Nickname') . '</span>' .$vcard['vCardNickname'].'</li>'
		        .'<li><span>'.t('Name given') . '</span>' .$vcard['vCardNGiven'].'</li>'
		        .'<li><span>'.t('Website') . '</span><a href="'.$vcard['vCardUrl'].'">' .str_replace($cleanurl, "", $vcard['vCardUrl']).'</a></li>'
		    .'</ul><br /><br />'
		    .'<h3>'.t('About me').'</h3>'
		    .'<div id="description">'.$vcard['vCardDesc'].'</div><br />';*/
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
		<div id="friendinfos">
		    <a class="button tiny" href="#" id="friendremove" onclick="<?php $this->callAjax("ajaxRemoveContact", "'".$_GET['f']."'"); ?>"><?php echo t('Remove this contact'); ?></a>
					<div class="config_button" onclick="<?php $this->callAjax('ajaxRefreshVcard', "'".$_GET['f']."'");?>"></div>
			<!--<h3><?php echo t('Contact informations'); ?></h3>-->
				<?php 
					if(isset($_GET['f']))
						echo $this->prepareInfos(Cache::c('vcard'.$_GET['f']));
					
				?>
                
		</div>
		<!--<div style="clear: left; width: 0px; height: 0px;"></div>-->
        <?php
    }
}
