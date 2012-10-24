<?php

/**
 * @package Widgets
 *
 * @file Roster.php
 * This file is part of MOVIM.
 *
 * @brief The Roster widget
 *
 * @author Jaussoin Timothée <edhelas@gmail.com>
 *
 * @version 1.0
 * @date 30 August 2010
 *
 * Copyright (C)2010 MOVIM project
 *
 * See COPYING for licensing information.
 */

class ContactCard extends WidgetCommon
{

    function WidgetLoad()
    {
    	$this->addcss('contactcard.css');
		$this->registerEvent('vcard', 'onVcard');
    }

    function onVcard($contact)
    {
        $html = $this->prepareContactCard($contact);
        RPC::call('movim_fill', 'contactcard', RPC::cdata($html));
    }

    function prepareContactCard($contact)
    {
        $gender = getGender();
        $marital = getMarital();

        $html .= '
            <form name="vcard" id="vcardform" class="protect red"><br />
            <h1>'.t('Profile').'</h1><br />
                <fieldset>
                    <legend>'.t('General Informations').'</legend>';
                    
            if($this->testIsSet($contact->getData('fn')))
            $html .= '<div class="element simple">
                        <label for="fn">'.t('Name').'</label>
                        <span>'.$contact->getData('fn').'</span>
                      </div>';

            if($this->testIsSet($contact->getData('name')))                        
            $html .= '<div class="element simple">
                        <label for="name">'.t('Nickname').'</label>
                        <span>'.$contact->getData('name').'</span>
                      </div>';
                      
            if($contact->getData('date') != '0000-00-00' && $this->testIsSet($contact->getData('date')))
            $html .= '<div class="element simple">
                        <label for="day">'.t('Date of Birth').'</label>
                        <span>'.date('j M Y',strtotime($contact->getData('date'))).'</span>
                      </div>';
            
            if($contact->getData('gender') != 'N' && $this->testIsSet($contact->getData('gender')))
            $html .= '<div class="element simple">
                        <label for="gender">'.t('Gender').'</label>
                        <span>'.$gender[$contact->getData('gender')].'</span>
                      </div>';
       
            if($contact->getData('marital') != 'none' && $this->testIsSet($contact->getData('marital')))               
            $html .= '<div class="element simple">
                        <label for="marital">'.t('Marital Status').'</label>
                        <span>'.$marital[$contact->getData('marital')].'</span>
                      </div>';
         
            if($this->testIsSet($contact->getData('email')))
            $html .= '<div class="element simple">
                        <label for="url">'.t('Email').'</label>
                        <a target="_blank" href="mailto:'.$contact->getData('email').'">'.$contact->getData('email').'</a>
                      </div>';
            if($this->testIsSet($contact->getData('url')))
            $html .= '<div class="element simple">
                        <label for="url">'.t('Website').'</label>
                        <a target="_blank" href="'.$contact->getData('url').'">'.$contact->getData('url').'</a>
                      </div>';
              
            if($this->testIsSet($contact->getData('desc')) && prepareString($contact->getData('desc')) != '')
            $html .= '<div class="element large simple">
                        <label for="desc">'.t('About Me').'</label>
                        <span>'.prepareString($contact->getData('desc')).'</span>
                      </div>';
                      
            if($this->testIsSet($contact->getData('adrlocality')) ||
               $this->testIsSet($contact->getData('adrcountry'))) {
                $html .= '</fieldset>
                            <br />
                          <fieldset>
                            <legend>'.t('Geographic Position').'</legend>';
                            
                if($this->testIsSet($contact->getData('adrlocality'))) {
                    $locality .= '<div class="element simple">
                                <label for="desc">'.t('Locality').'</label>
                                <span>'.$contact->getData('adrlocality');
                    if($contact->getData('adrpostalcode') != 0)
                        $locality .= ' ('.$contact->getData('adrpostalcode').')';
                    $locality .= '</span>
                              </div>';
                    
                    $html .= $locality;
                }
                            
                if($this->testIsSet($contact->getData('adrcountry')))
                $html .= '<div class="element simple">
                            <label for="desc">'.t('Country').'</label>
                            <span>'.$contact->getData('adrcountry').'</span>
                          </div>';
            }
                      
            $html .= '</fieldset>
                      <div class="config_button" onclick="'.$this->genCallWidget("ContactSummary","ajaxRefreshVcard", "'".$contact->getData('jid')."'").'"></div>
                </form>';
        
        return $html;
    }

    function build()
    {
        $query = Contact::query()->select()
                           ->where(array(
                                   'jid' => $_GET['f']));
        $contact = Contact::run_query($query);
        ?>
        <div class="tabelem" title="<?php echo t('Profile'); ?>" id="contactcard" >
            <?php
            if(isset($contact[0]))
                echo $this->prepareContactCard($contact[0]);
            ?>
        </div>
        <?php
    }
}
