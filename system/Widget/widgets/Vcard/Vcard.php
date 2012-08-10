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
		$this->registerEvent('myvcardvalid', 'onMyVcardReceived');
		$this->registerEvent('myvcardinvalid', 'onMyVcardNotReceived');
    	$this->addcss('vcard.css');
    	$this->addjs('vcard.js');
    }
    
    function onMyVcardReceived()
    {
		$html = $this->prepareInfos();
        RPC::call('movim_fill', 'vcard', RPC::cdata($html));
    }
    
    function onMyVcardNotReceived($error)
    {
		$html = $this->prepareInfos($error);
        RPC::call('movim_fill', 'vcard', RPC::cdata($html));
    }
    
	function ajaxVcardSubmit($vcard)
    {
        //foreach($vcard as $key => $value)
        //    $vcard[$key] = rawurldecode($value);
	    # Format it ISO 8601:
	    $vcard['date'] = $vcard['year'].'-'.$vcard['month'].'-'.$vcard['day'];
        unset($vcard['year']);
        unset($vcard['month']);
        unset($vcard['day']);
        
        $c = new \Contact();

        $query = \Contact::query()->select()
                                   ->where(array(
                                           'key' => $this->user->getLogin(),
                                           'jid' => $this->user->getLogin()));
        $data = \Contact::run_query($query);

        if($data) {
            $c = $data[0];
        }

        $c->key->setval($this->user->getLogin());
        $c->jid->setval($this->user->getLogin());
        
        $date = strtotime($vcard['date']);
        $c->date->setval(date('Y-m-d', $date)); 
        
        $c->name->setval($vcard['name']);
        $c->fn->setval($vcard['fn']);
        $c->url->setval($vcard['url']);
        
        $c->gender->setval($vcard['gender']);
        $c->marital->setval($vcard['marital']);
        
        if($c->rostersubscription->getval() == false)
            $c->rostersubscription->setval('none');
        
        $c->phototype->setval($vcard['phototype']);
        $c->photobin->setval($vcard['photobin']);
        
        $c->desc->setval(trim($vcard['desc']));
        
        $c->vcardreceived->setval(0);
        $c->public->setval(0);
        
        $c->run_query($c->query()->save($c));
        
        $r = new moxl\VcardSet();
        $r->setData($vcard)->request();
	}
    
    function prepareInfos($error = false) {
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
            
            if($error == 'vcardfeaturenotimpl') {
                $html .= '
                    <div class="error">'.t("Profil not updated : Your server does not support the vCard feature").'</div>';
            }
        
            $html .= '
            <form name="vcard"><br />
                <fieldset class="protect red">
                    <legend>'.t('General Informations').'</legend>';
                    
            $html .= '<div class="element"><span>'.t('Name').'</span>
                        <input type="text" name="fn" class="content" value="'.$me->getData('fn').'">
                      </div>';
            $html .= '<div class="element"><span>'.t('Nickname').'</span>
                        <input type="text" name ="name" class="content" value="'.$me->getData('name').'">
                      </div>';
                      
            $html .= '<div class="element"><span>'.t('Date of Birth').'</span>';
            $html .= '<select name="day" class="datepicker"><option value="-1">'.t('Day').'</option>';
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
            $html .= '<select name="month" class="datepicker"><option value="-1">'.t('Month').'</option>';
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
            $html .= '<select name="year" class="datepicker"><option value="-1">'.t('Year').'</option>';
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
                        <select name="gender">';
                        foreach(getGender() as $key => $value) {
                            $html .= '<option ';
                            if($key == $me->getData('gender'))
                                $html .= 'selected ';
                            $html .= 'value="'.$key.'">'.$value.'</option>';
                        }
            $html .= '  </select>
                      </div>';
                      
            $html .= '<div class="element"><span style="padding-top: 5px;">'.t('Marital Status').'</span>
                        <select name="marital">';
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
                        <input type="url" name ="url" class="content" value="'.$me->getData('url').'">
                      </div>';
                      
            $html .= '<br />
                      <div class="element"><span>'.t('Avatar').'</span>
                        <img id="vCardPhotoPreview" src="data:'.$me->getData('phototype').';base64,'.$me->getData('photobin').'">
                        <input type="hidden" name="phototype"  value="'.$me->getData('phototype').'">
                        <input type="hidden" name="photobin"  value="'.$me->getData('photobin').'"><br />
                        <span></span><input type="file" onchange="vCardImageLoad(this.files);">
                      </div>';
                      
            $html .= '<br />
                      <div class="element"><span>'.t('About Me').'</span>
                        <textarea name ="desc" class="content" onkeyup="movim_textarea_autoheight(this);">'.trim($me->getData('desc')).'</textarea>
                      </div>';
                      
            $html .= '</fieldset>';                  
            /*$html .= '<br />
                <fieldset>
                    <legend>'.t('Geographic Position').'</legend>';
		    $html .= '<div class="warning"><a class="button tiny" style="float: right;" onclick="getPos(this);">Récupérer ma position</a></div>';
		    $html .= '<div id="geolocation"></div>';
            $html .= '<div class="element"><span>'.t('Latitude').'</span>
                        <input type="text" name="lat" class="content" value="Latitude" readonly>
                      </div>';
            $html .= '<div class="element"><span>'.t('Longitude').'</span>
                        <input type="text" name="long" class="content" value="Longitude" readonly>
                      </div>';

            $html .= '<hr />
                </fieldset>';*/

		    $html .= '<input 
                            value="'.t('Submit').'" 
                            onclick="
                                '.$submit.' this.value = \''.t('Submitting').'\'; 
                                this.className=\'button icon loading merged right\'" 
                            class="button icon ';
                if($error)
                    $html .= 'no';
                else
                    $html .= 'yes';
            $html .=        ' merged right" 
                            type="button" style="float: right;"
                        > ';
            $html .= '<input type="reset" value="'.t('Reset').'" class="button icon no merged left" style="float: right;">';


            $html .= '
            </form>';
        } else { 
            $html .= '<script type="text/javascript">setTimeout(\''.$this->genCallAjax('ajaxGetVcard').'\', 500);</script>';
        }
        $html .= '<div class="config_button" onclick="'.$this->genCallAjax('ajaxGetVcard').'"></div>';

        return $html;
    }
    
    function ajaxGetVcard()
    {
        $r = new moxl\VcardGet();
        $r->setTo($this->user->getLogin())->request();
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
