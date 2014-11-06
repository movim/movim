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

    function load()
    {
    	$this->addcss('contactcard.css');
		$this->registerEvent('vcard', 'onVcard');
    }
    
    function display()
    {
        $cd = new \Modl\ContactDAO();
        $this->view->assign('contact', $cd->get($_GET['f']));
    }

    function onVcard($contact)
    {
        $html = $this->prepareContactCard($contact);
        RPC::call('movim_fill', 'contactcard', $html);
    }

    function prepareContactCard($contact)
    {
        $gender = getGender();
        $marital = getMarital();
        
        $html = '';

        $html .= '
            <form name="vcard" id="vcardform">
            <h1>'.$this->__('Profile').'</h1>
                <fieldset>
                    <legend>'.$this->__('general.legend').'</legend>';
                    
            if($this->testIsSet($contact->fn))
            $html .= '<div class="element simple">
                        <label for="fn">'.$this->__('general.name').'</label>
                        <span>'.$contact->fn.'</span>
                      </div>';

            if($this->testIsSet($contact->name))                        
            $html .= '<div class="element simple">
                        <label for="name">'.$this->__('general.nickname').'</label>
                        <span>'.$contact->name.'</span>
                      </div>';

            if(strtotime($contact->date) != 0)
            $html .= '<div class="element simple">
                        <label for="day">'.$this->__('general.date_of_birth').'</label>
                        <span>'.prepareDate(strtotime($contact->date), false).'</span>
                      </div>';
            
            if($contact->gender != 'N' && $this->testIsSet($contact->gender))
            $html .= '<div class="element simple">
                        <label for="gender">'.$this->__('general.gender').'</label>
                        <span>'.$gender[(string)$contact->gender].'</span>
                      </div>';
       
            if($contact->marital != 'none' && $this->testIsSet($contact->marital))               
            $html .= '<div class="element simple">
                        <label for="marital">'.$this->__('general.marital').'</label>
                        <span>'.$marital[(string)$contact->marital].'</span>
                      </div>';

            if($this->testIsSet($contact->email)) {
                if(filter_var($contact->email, FILTER_VALIDATE_EMAIL)) {
                    $html .= '<div class="element simple">
                                <label for="url">'.$this->__('general.email').'</label>
                                <img src="'.$contact->getPhoto('email').'"/>
                              </div>';
                } else {
                    $html .= '<div class="element simple">
                                <label for="url">'.$this->__('general.email').'</label>
                                '.$contact->email.'
                              </div>';                    
                }
            }
                      
            if($this->testIsSet($contact->url)) {
                if(filter_var($contact->url, FILTER_VALIDATE_URL)) {
                    $html .= '<div class="element simple">
                                <label for="url">'.$this->__('general.legend').'</label>
                                <a target="_blank" href="'.$contact->url.'">'.$contact->url.'</a>
                              </div>';
                } else {
                    $html .= '<div class="element simple">
                                <label for="url">'.$this->__('general.legend').'</label>
                                '.$contact->url.'
                              </div>';     
                }
            }
              
            if($this->testIsSet($contact->description) && prepareString($contact->description) != '')
            $html .= '<div class="element large simple">
                        <label for="desc">'.$this->__('general.about').'</label>
                        <span style="white-space: pre-wrap;">'.prepareString($contact->description).'</span>
                      </div>';
                      
            if($this->testIsSet($contact->adrlocality) ||
               $this->testIsSet($contact->adrcountry)) {
                $html .= '</fieldset>
                            <br />
                          <fieldset>
                            <legend>'.$this->__('position.legend').'</legend>';
                            
                if($this->testIsSet($contact->adrlocality)) {
                    $locality = '<div class="element simple">
                                <label for="adrlocality">'.$this->__('position.locality').'</label>
                                <span>'.$contact->adrlocality;
                    if($contact->adrpostalcode != 0)
                        $locality .= ' ('.$contact->adrpostalcode.')';
                    $locality .= '</span>
                              </div>';
                    
                    $html .= $locality;
                }
                            
                if($this->testIsSet($contact->adrcountry))
                $html .= '<div class="element simple">
                            <label for="adrcountry">'.$this->__('position.coutry').'</label>
                            <span>'.$contact->adrcountry.'</span>
                          </div>';
            }
                      
            $html .= '</fieldset>
                      <div class="config_button" onclick="'.$this->genCallWidget("ContactSummary","ajaxRefreshVcard", "'".$contact->jid."'").'"></div>
                </form>';
        
        return $html;
    }
}
