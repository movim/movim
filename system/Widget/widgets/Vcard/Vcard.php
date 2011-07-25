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

class Vcard extends WidgetBase
{
    function WidgetLoad()
    {
		$this->registerEvent('myvcardreceived', 'onMyVcardReceived');
    	$this->addcss('vcard.css');
    	$this->addjs('vcard.js');
    }
    
    function onMyVcardReceived($vcard)
    {
		$html = $this->prepareInfos($vcard);
        RPC::call('movim_fill', 'vcard', RPC::cdata($html));
    }
    
/*    private function displayIf($element, $title, $html = false) {
        if(!$html) $html = $element;
        if(isset($element)) 
                return '<div class="element"><span>'.$title.'</span><div class="content">'.$html.'</div></div>';
    }*/
    
	function ajaxVcardSubmit($vcard) {
		$xmpp = Jabber::getInstance();
		$xmpp->updateVcard($vcard);
	}
    
    function prepareInfos($vcard) {
		$submit = $this->genCallAjax('ajaxVcardSubmit', "movim_parse_form('vcard')");
        $html ='
        <form name="vcard"><br />
            <fieldset>
                <legend>'.t('General Informations').'</legend>';
                
        $html .= '<div class="element"><span>'.t('Name').'</span>
                    <input type="text" name="vCardFN" class="content" value="'.$vcard["vCardFN"].'">
                  </div>';
        $html .= '<div class="element"><span>'.t('Nickname').'</span>
                    <input type="text" name ="vCardNickname" class="content" value="'.$vcard["vCardNickname"].'">
                  </div>';
        $html .= '<div class="element"><span>'.t('Date of Birth').' YYYY-MM-DD</span>
                    <input type="text" name ="vCardBDay" class="content" value="'.$vcard["vCardBDay"].'">
                  </div>';
                  
        $html .= '<br />
                  <div class="element"><span>'.t('Website').'</span>
                    <input type="text" name ="vCardUrl" class="content" value="'.$vcard["vCardUrl"].'">
                  </div>';
                  
        $html .= '</fieldset>';                  
        $html .= '<br />
            <fieldset>
                <legend>'.t('Geographic Position').'</legend>';
		$html .= '<div class="warning">'.t('Renseigner votre position géographique peut fortement porter atteinte à votre vie privé, utilisez toujours cette option qu\'en cas de nécessité').'<a class="button tiny" style="float: right;" onclick="getPos(this);">Récupérer ma position</a></div>';
		$html .= '<div id="geolocation"></div>';
        $html .= '<div class="element"><span>'.t('Latitude').'</span>
                    <input type="text" name="vCardLat" class="content" value="Latitude" readonly>
                  </div>';
        $html .= '<div class="element"><span>'.t('Longitude').'</span>
                    <input type="text" name="vCardLong" class="content" value="Longitude" readonly>
                  </div>';
        /*
        $html .= $this->displayIf($vcard["vCardFN"], t('Name'));
        $html .= $this->displayIf($vcard["vCardNickname"], t('Nickname'));
        $html .= $this->displayIf($vcard["from"], t('Adress'));
        $html .= $this->displayIf($vcard["vCardBDay"], t('Date of Birth'), date('j F Y',strtotime($vcard["vCardBDay"])));
        
        $html .= '<br />';
        
        $html .= $this->displayIf($vcard["vCardUrl"], t('Website'), '<a href="'.$vcard["vCardUrl"].'">'.$vcard["vCardUrl"].'</a>');
        $html .= $this->displayIf($vcard["vCardPhotoType"], t('Avatar'), '<img src="data:'.$vcard["vCardPhotoType"].';base64,'.$vcard["vCardPhotoBinVal"].'">');
        
        $html .= '<br />';
        $html .= $this->displayIf($vcard["vCardDesc"], t('About Me'));*/
        $html .= '<hr />';
		$html .= ' <input value="'.t('Submit').'" onclick="'.$submit.'" id="right" type="button"> ';
        $html .= '
            </fieldset>
        </form>';
        
        //var_dump($vcard);
        //["vCardBDay"]
        //var_dump($vcard);
        //var_dump($vcard);
		/*$html = '<div id="friendavatar">';
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
        
            $html .= '<div id="frienddescription"><p>'.$status.'</p></div>';*/
        
        

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

    function build()
    {
        ?>
		<div class="tabelem" title="<?php echo t('Profile'); ?>" id="vcard">
				<?php 
					echo $this->prepareInfos(Cache::c('myvcard'));
				?>
                
		</div>
        <?php
    }
}
