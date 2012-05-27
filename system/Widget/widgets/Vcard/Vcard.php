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
		$this->registerEvent('myvcard', 'onMyVcardReceived');
    	$this->addcss('vcard.css');
    	$this->addjs('vcard.js');
        
        //$this->cached = true;
    }
    
    function onMyVcardReceived()
    {
		$html = $this->prepareInfos();
        RPC::call('movim_fill', 'vcard', RPC::cdata($html));
    }
    
	function ajaxVcardSubmit($vcard)
    {
        foreach($vcard as $key => $value)
            $vcard[$key] = rawurldecode($value);
	    # Format it ISO 8601:
	    $vcard['vCardBDay'] = $vcard['vCardBYear'].'-'.$vcard['vCardBMonth'].'-'.$vcard['vCardBDay'];
		$this->xmpp->updateVcard($vcard);
	}
    
    function prepareInfos() {
        $query = Contact::query()
                            ->where(array('key' => $this->user->getLogin(), 'jid' => $this->user->getLogin()));
        $me = Contact::run_query($query);
        
        $submit = $this->genCallAjax('ajaxVcardSubmit', "movim_parse_form('vcard')");
        
        if(!isset($me[0])) { 
        ?>
            <div class="warning">
                <?php echo "It's your first time on Movim! To fill in a 
                few informations about you and display them to your 
                contacts, create your virtual card by clicking the next button."; ?>
                <a 
                onclick="<?php echo $submit; ?>" style="float: right; margin: 5px 0px 0px 0px;"
                href="#" class="button big icon add"><?php echo t("Create my vCard"); ?></a><br />
            </div>

        <?php
        }
    

        if(isset($me[0])) {
            $me = $me[0];
        
            $html .= '
            <form name="vcard"><br />
                <fieldset class="protect red">
                    <legend>'.t('General Informations').'</legend>';
                    
            $html .= '<div class="element"><span>'.t('Name').'</span>
                        <input type="text" name="vCardFN" class="content" value="'.$me->getData('fn').'">
                      </div>';
            $html .= '<div class="element"><span>'.t('Nickname').'</span>
                        <input type="text" name ="vCardNickname" class="content" value="'.$me->getData('name').'">
                      </div>';
                      
            $html .= '<div class="element"><span>'.t('Date of Birth').'</span>';
            $html .= '<select name="vCardBDay" class="datepicker"><option value="-1">'.t('Day').'</option>';
            for($i=1; $i<= 31; $i++){
                if($i < 10){
                    $j = '0'.$i;
                } else {
                    $j = $i;
                }
                if($i == substr( $me->getData('date'), 8)) {
                    $html .= '<option value="'.$j.'" selected>'.$j.'</option>';
                } else {
                    $html .= '<option value="'.$j.'">'.$j.'</option>';
                }
            }
            $html .= '</select>';
            $html .= '<select name="vCardBMonth" class="datepicker"><option value="-1">'.t('Month').'</option>';
            for($i=1; $i<= 12; $i++){
                if($i < 10){
                    $j = '0'.$i;
                } else {
                    $j = $i;
                }
                if($i == substr( $me->getData('date'), 5, 2)) {
                    $html .= '<option value="'.$j.'" selected>'.$j.'</option>';
                } else {
                    $html .= '<option value="'.$j.'">'.$j.'</option>';
                }
            }
            $html .= '</select>';
            $html .= '<select name="vCardBYear" class="datepicker"><option value="-1">'.t('Year').'</option>';
            for($i=date('o'); $i>= 1920; $i--){
                if($i == substr( $me->getData('date'), 0, 4)) {
                    $html .= '<option value="'.$i.'" selected>'.$i.'</option>';
                } else {
                    $html .= '<option value="'.$i.'">'.$i.'</option>';
                }
            }
            $html .= '</select></div>';
            
            $html .= '<br />
                      <div class="element"><span style="padding-top: 5px;">'.t('Gender').'</span>
                        <select name="vCardGender">';
                        foreach(getGender() as $key => $value) {
                            $html .= '<option ';
                            if($key == $me->getData('gender'))
                                $html .= 'selected ';
                            $html .= 'value="'.$key.'">'.$value.'</option>';
                        }
            $html .= '  </select>
                      </div>';
                      
            $html .= '<div class="element"><span style="padding-top: 5px;">'.t('Marital Status').'</span>
                        <select name="vCardMaritalStatus">';
                        foreach(getMarital() as $key => $value) {
                            $html .= '<option ';
                            if($key == $me->getData('marital'))
                                $html .= 'selected ';
                            $html .= 'value="'.$key.'">'.$value.'</option>';
                        }
            $html .= '  </select>
                      </div>';
         
            $html .= '<br />
                      <div class="element"><span>'.t('Website').'</span>
                        <input type="url" name ="vCardUrl" class="content" value="'.$me->getData('url').'">
                      </div>';
                      
            $html .= '<br />
                      <div class="element"><span>'.t('Avatar').'</span>
                        <img id="vCardPhotoPreview" src="data:'.$me->getData('phototype').';base64,'.$me->getData('photobin').'">
                        <input type="hidden" name="vCardPhotoType"  value="'.$me->getData('phototype').'">
                        <input type="hidden" name="vCardPhotoBinVal"  value="'.$me->getData('photobin').'"><br />
                        <span></span><input type="file" onchange="vCardImageLoad(this.files);">
                      </div>';
                      
            $html .= '<br />
                      <div class="element"><span>'.t('About Me').'</span>
                        <textarea name ="vCardDesc" class="content" >'.$me->getData('desc').'</textarea>
                      </div>';
                      
            $html .= '</fieldset>';                  
    /*        $html .= '<br />
                <fieldset>
                    <legend>'.t('Geographic Position').'</legend>';
		    $html .= '<div class="warning"><a class="button tiny" style="float: right;" onclick="getPos(this);">Récupérer ma position</a></div>';
		    $html .= '<div id="geolocation"></div>';
            $html .= '<div class="element"><span>'.t('Latitude').'</span>
                        <input type="text" name="vCardLat" class="content" value="Latitude" readonly>
                      </div>';
            $html .= '<div class="element"><span>'.t('Longitude').'</span>
                        <input type="text" name="vCardLong" class="content" value="Longitude" readonly>
                      </div>';*/

            $html .= '<hr />';

		    $html .= '<input value="'.t('Submit').'" onclick="'.$submit.' this.value = \''.t('Submitting').'\'; this.className=\'button icon loading merged right\'" class="button icon yes merged right" type="button" style="float: right;"> ';
            $html .= '<input type="reset" value="'.t('Reset').'" class="button icon no merged left" style="float: right;">';


            $html .= '
                </fieldset>
            </form>';
        } else { 
            $html .= '<script type="text/javascript">setTimeout(\''.$this->genCallAjax('ajaxGetVcard').'\', 500);</script>';
        }
        $html .= '<div class="config_button" onclick="'.$this->genCallAjax('ajaxGetVcard').'"></div>';

        return $html;
    }
    
    function ajaxGetVcard()
    {
		$this->xmpp->getVcard();
    }

    function build()
    {
        ?>
		<div class="tabelem" title="<?php echo t('Edit my Profile'); ?>" id="vcard">
			<?php 
				echo $this->prepareInfos();
			?>
		</div>
        <?php
    }
}
