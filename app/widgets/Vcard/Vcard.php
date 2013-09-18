<?php

/**
 * @package Widgets
 *
 * @file Vcard.php
 * This file is part of MOVIM.
 * 
 * @brief A widget which display all the infos of a contact
 *
 * @author Timothée    Jaussoin <edhelas_at_gmail_dot_com>
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
        $this->registerEvent('myvcard', 'onMyVcardReceived');
        $this->addcss('vcard.css');
        $this->addjs('vcard.js');
    }
    
    function onMyVcardReceived()
    {
        $html = $this->prepareInfos(true);

        RPC::call('movim_fill', 'vcard', $html);
        Notification::appendNotification(t('Profile Updated'), 'success');
        RPC::commit();
    }
    
    function onMyVcardNotReceived($error)
    {
        $html = $this->prepareInfos(true);
        RPC::call('movim_fill', 'vcard', $html);
    }
    
    function ajaxVcardSubmit($vcard)
    {
        # Format it ISO 8601:
        if($vcard->year->value  != -1 
        && $vcard->month->value != -1 
        && $vcard->day->value   != -1)
            $vcard->date->value = 
                    $vcard->year->value.'-'.
                    $vcard->month->value.'-'.
                    $vcard->day->value;
            
        unset($vcard->year->value);
        unset($vcard->month->value);
        unset($vcard->day->value);
        
        $c = new modl\Contact();
            
        $c->jid     = $this->user->getLogin();
        
        if(isset($vcard->date->value)) {
            $date = strtotime($vcard->date->value);
            $c->date = date('Y-m-d', $date);
        } 
        
        $c->name    = $vcard->name->value;
        $c->fn      = $vcard->fn->value;
        $c->url     = $vcard->url->value;
        
        $c->gender  = $vcard->gender->value;
        $c->marital = $vcard->marital->value;

        $c->email   = $vcard->email->value;
        
        $c->adrlocality     = $vcard->locality->value;
        $c->adrcountry      = $vcard->country->value;
        
        $c->phototype       = $vcard->phototype->value;
        $c->photobin        = $vcard->photobin->value;
        
        $c->description     = trim($vcard->desc->value);

        if($vcard->privacy->value == true)
            \modl\Privacy::set($c->jid, 1);
        else
            \modl\Privacy::set($c->jid, 0);
            
        $cd = new modl\ContactDAO();
        $cd->set($c);
        
        $c->createThumbnails();
        
        $r = new moxl\VcardSet();
        $r->setData($vcard)->request();
    }
    
    function prepareInfos($error = false) {
                
        $cd = new \modl\ContactDAO();

        $me = $cd->get($this->user->getLogin());

        $submit = $this->genCallAjax('ajaxVcardSubmit', "movim_form_to_json('vcard')");
            
        $html = '';

        if($me == null) { 
        ?>
            <div class="message info">
                <?php echo t("It's your first time on Movim! To fill in a 
                few information about you and display them to your 
                contacts, create your virtual card by clicking the next button."); ?>
            </div>
            
            <!--<a 
                onclick="<?php echo $this->genCallAjax('ajaxGetVcard'); ?>"
                class="button color green icon add"><?php echo t("Create my vCard"); ?></a>-->
        <?php
            if(!$error)
                $html .= '
                    <script type="text/javascript">setTimeout(\''.$this->genCallAjax('ajaxGetVcard').'\', 2000);</script>';
        }
        
        /*if($error == 'vcardfeaturenotimpl') {
            $html .= '
                <div class="message error">'.t("Profile not updated : Your server does not support the vCard feature").'</div>';
        }
        
        if($error == 'vcardbadrequest') {
            $html .= '
                <div class="message error">'.t("Profile not updated : Request error").'</div>';
        }*/

        if($me->privacy == '1')
            $color = 'black';
        else
            $color =  'red';
    
        $html .= '
        <form name="vcard" id="vcardform"><br />
            <fieldset>
                <div class="protect '.$color.'" title="'.getFlagTitle($color).'"></div>
                <legend>'.t('General Informations').'</legend>';
                
            $html .= '<div class="element">
                        <label for="fn">'.t('Name').'</label>
                        <input type="text" name="fn" class="content" value="'.$me->fn.'">
                      </div>';
                      
            $html .= '<div class="element">
                        <label for="name">'.t('Nickname').'</label>
                        <input type="text" name="name" class="content" value="'.$me->name.'">
                      </div>';
                      
            $html .= '<div class="element">
                        <label for="email">'.t('Email').'</label>
                        <input type="email" name="email" class="content" value="'.$me->email.'">
                      </div>';
                      
            $html .= '<div class="element ">
                        <label for="day">'.t('Date of Birth').'</label>';
                    
            $html .= '
                    <div class="select" style="width: 33%; float: left;">
                        <select name="day" class="datepicker">
                        <option value="-1">'.t('Day').'</option>';
                            for($i=1; $i<= 31; $i++){
                                if($i < 10){
                                    $j = '0'.$i;
                                } else {
                                    $j = $i;
                                }
                                if($i == substr( $me->date, 8)) {
                                    $html .= '<option value="'.$j.'" selected>'.$j.'</option>';
                                } else {
                                    $html .= '<option value="'.$j.'">'.$j.'</option>';
                                }
                            }
            $html .= '  </select>
                    </div>';
                    

            $html .= '
                    <div class="select" style="width: 34%; float: left;">
                        <select name="month" class="datepicker">
                        <option value="-1">'.t('Month').'</option>';
                            for($i=1; $i<= 12; $i++){
                                if($i < 10){
                                    $j = '0'.$i;
                                } else {
                                    $j = $i;
                                }
                                if($i == substr( $me->date, 5, 2)) {
                                    $html .= '<option value="'.$j.'" selected>'.$j.'</option>';
                                } else {
                                    $html .= '<option value="'.$j.'">'.$j.'</option>';
                                }
                            }
            $html .= '  </select>
                    </div>';
                    
            $html .= '
                    <div class="select" style="width: 33%; float: left;">
                        <select name="year" class="datepicker">
                        <option value="-1">'.t('Year').'</option>';
                            for($i=date('o'); $i>= 1920; $i--){
                                if($i == substr( $me->date, 0, 4)) {
                                    $html .= '<option value="'.$i.'" selected>'.$i.'</option>';
                                } else {
                                    $html .= '<option value="'.$i.'">'.$i.'</option>';
                                }
                            }
            $html .= '  </select>
                    </div>
                </div>';

        
            $html .= '<div class="element">
                        <label for="gender">'.t('Gender').'</label>
                        <div class="select"><select name="gender">';
                        foreach(getGender() as $key => $value) {
                            $html .= '<option ';
                            if($key == $me->gender)
                                $html .= 'selected ';
                            $html .= 'value="'.$key.'">'.$value.'</option>';
                        }
            $html .= '  </select></div>
                      </div>';
                      
            $html .= '<div class="element"><label for="marital">'.t('Marital Status').'</label>
                        <div class="select"><select name="marital">';
                        foreach(getMarital() as $key => $value) {
                            $html .= '<option ';
                            if($key == $me->marital)
                                $html .= 'selected ';
                            $html .= 'value="'.$key.'">'.$value.'</option>';
                        }
            $html .= '  </select></div>
                      </div>';
                   
            $html .= '<div class="element"><label for="avatar">'.t('Avatar').'</label>
                        <img id="vCardPhotoPreview" src="data:'.$me->phototype.';base64,'.$me->photobin.'">
                        <br /><span id="picturesize" class="clean"></span><br /><br />
                        
                        <input type="file" onchange="vCardImageLoad(this.files);">

                        <input type="hidden" name="phototype"  value="'.$me->phototype.'"/>
                        <input type="hidden" name="photobin"  value="'.$me->photobin.'"/>
                      </div>';
            
            $html .= '<div class="element" id="camdiv">
                        <label for="url">'.t('Webcam').'</label>
                        <video id="runningcam" class="squares" autoplay></video>
                        <canvas style="display:none;"></canvas>
                        
                        <a 
                            id="shoot" 
                            class="button icon preview color green" 
                            onclick="return false;">'.
                            t("Cheese !").'
                        </a>
                        <a
                            id="capture" 
                            class="button icon image color purple" 
                            onclick="
                                showVideo();
                                return false;">'.
                            t("Take a webcam snapshot").'
                        </a>
                    </div>';    
                                 
            $html .= '<div class="element large"><label for="url">'.t('Website').'</label>
                        <input type="url" name ="url" class="content" value="'.$me->url.'">
                      </div>';
                  
            $html .= '<div class="element large"><label for="desc">'.t('About Me').'</label>
                            <textarea name="desc" id="desctext" class="content" onkeyup="movim_textarea_autoheight(this);">'.trim($me->description).'</textarea>
                      </div>';
                  
        $html .= '</fieldset><br />'; 
                  
        $html .= '
            <fieldset>
                <legend>'.t('Geographic Position').'</legend>';
                
            $html .= '<div class="element"><label for="url">'.t('Locality').'</label>
                        <input type="text" type="locality" name ="locality" class="content" value="'.$me->adrlocality.'">
                      </div>';
                      
            $html .= '<div class="element"><label for="country">'.t('Country').'</label>
                        <div class="select">
                            <select name="country">
                                <option value=""></option>';
                        $ctry = $me->adrcountry;
                        foreach(getCountries() as $value) {
                            $html .= '<option ';
                            if($value == $ctry)
                                $html .= 'selected ';
                            $html .= 'value="'.$value.'">'.$value.'</option>';
                        }
            $html .= '      </select>
                        </div>
                      </div>';
                      
        $html .= '
            </fieldset>';   
                  
        $html .= '<fieldset>
                    <legend>'.t('Privacy Level').'</legend>';

            if($me->privacy == 1)
                $checked = 'checked="true"';
            else
                $checked =  '';
            
            $html .= '';
                      
            $html .= '<div class="element large">
                        <div class="message info" style="float: right; width: 70%; margin-bottom: 1.5em;">
                            '.t('Please pay attention ! By making your profile public, all the information listed above will be available for all the Movim users and on the whole Internet.').'
                        </div>
                        <label>'.t('Is this profile public ?').'</label>
                          <div class="checkbox">
                            <input type="checkbox" id="privacy" name="privacy" '.$checked.'/>
                            <label for="privacy"></label>
                          </div>
                      </div>';
                  
        $html .= '</fieldset>'; 
        
        $html .= '<hr /><br />';
        $html .= '<a
                        onclick="
                            '.$submit.' this.value = \''.t('Submitting').'\'; 
                            this.className=\'button color orange icon loading merged right\';
                            this.onclick=null;" 
                        class="button icon ';
            if($error)
                $html .= 'no';
            else
                $html .= 'yes';
        $html .=        ' merged right color green" 
                        style="float: right;"
                    >'.t('Submit').'</a>';
        $html .= '<a onclick="document.querySelector(\'#vcardform\').reset();" class="button icon no merged left color orange" style="float: right;">'.t('Reset').'</a>';


        $html .= '
        </form>';

        $html .= '<div class="config_button" onclick="'.$this->genCallAjax('ajaxGetVcard').'"></div>';

        return $html;
    }
    
    function ajaxGetVcard()
    {
        $r = new moxl\VcardGet();
        $r->setTo($this->user->getLogin())
          ->setMe()
          ->request();
    }

    function build()
    {
        ?>
        <div class="tabelem paddedtop" title="<?php echo t('Edit my Profile'); ?>" id="vcard">
            <?php 
                echo $this->prepareInfos();
            ?>
            <div class="clear"></div>
        </div>
        <br />
        <br />
        <?php
    }
}
