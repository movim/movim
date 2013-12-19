<?php

/**
 * @package Widgets
 *
 * @file Vcard4.php
 * This file is part of MOVIM.
 * 
 * @brief A widget which display all the infos of a contact, vcard 4 version
 *
 * @author Timothée    Jaussoin <edhelas_at_gmail_dot_com>

 * Copyright (C)2013 MOVIM project
 * 
 * See COPYING for licensing information.
 */

class Vcard4 extends WidgetBase
{
    function WidgetLoad()
    {
        $this->registerEvent('myvcard4valid', 'onMyVcard4Received');
        $this->registerEvent('myvcard4invalid', 'onMyVcard4NotReceived');
        
        $cd = new \modl\ContactDAO();
        $me = $cd->get($this->user->getLogin());
        $this->view->assign('me',       $me);
        $this->view->assign('desc',     trim($me->description));
        $this->view->assign('gender',   getGender());
        $this->view->assign('marital',  getMarital());
        $this->view->assign('countries',getCountries());
        
        $this->view->assign(
            'submit',
            $this->genCallAjax('ajaxVcardSubmit', "movim_form_to_json('vcard4')")
            );
            
        $this->view->assign(
            'privacy',
            $this->genCallAjax('ajaxChangePrivacy', "this.checked")
            );

        // The datepicker arrays
        $days = $months = $years = array();
        for($i=1; $i<= 31; $i++) {
            if($i < 10){
                $j = '0'.$i;
            } else {
                $j = (string)$i;
            }
            $days[$i] = $j;
        }
        for($i=1; $i<= 12; $i++) {
            if($i < 10){
                $j = '0'.$i;
            } else {
                $j = (string)$i;
            }
            $m = getMonths();
            
            $months[$j] = $m[$i];
        }
        for($i=date('o'); $i>= 1920; $i--) { array_push($years, $i); }

        $this->view->assign('days',   $days);
        $this->view->assign('months', $months);
        $this->view->assign('years',  $years);
    }

    function onMyVcard4Received()
    {
        RPC::call('movim_button_reset', '#vcard4validate');
        Notification::appendNotification(t('Profile Updated'), 'success');
        RPC::commit();
    }
    
    function onMyVcard4NotReceived()
    {
        Notification::appendNotification(t('Profile Not Updated'), 'error');
        RPC::commit();
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

        $cd = new \modl\ContactDAO();
        $c = $cd->get($this->user->getLogin());

        if($c == null)
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

        $c->adrlocality     = $vcard->locality->value;
        $c->adrcountry      = $vcard->country->value;

        $c->email   = $vcard->email->value;
        
        $c->description     = trim($vcard->desc->value);
            
        $cd = new modl\ContactDAO();
        $cd->set($c);
        
        $r = new moxl\Vcard4Set();
        $r->setData($c)->request();

        $r = new moxl\VcardSet();
        $r->setData($vcard)->request();
    }

    function ajaxChangePrivacy($value)
    {
        if($value == true) {
            \modl\Privacy::set($this->user->getLogin(), 1);
            Notification::appendNotification(t('Your profile is now public'), 'success');
        } else {
            \modl\Privacy::set($this->user->getLogin(), 0);
            Notification::appendNotification(t('Your profile is now restricted'), 'success');
        }
    }
}
