<?php

/**
 * @package Widgets
 *
 * @file Subscribe.php
 * This file is part of MOVIM.
 *
 * @brief The account creation widget.
 *
 * @author Timothée Jaussoin <edhelas@gmail.com>
 *
 * @version 1.0
 * @date 25 November 2011
 *
 * Copyright (C)2010 MOVIM project
 *
 * See COPYING for licensing information.
 */

use Moxl\Xec\Action\Disco\Request;
use Moxl\Xec\Action\Register\Get;
use Moxl\Xec\Action\Register\Set;

class AccountNext extends WidgetBase {
    function load()
    {
        $this->addjs('accountnext.js');
        
        $this->registerEvent('register_get_handle', 'onForm');
        $this->registerEvent('register_set_handle', 'onRegistered');
        $this->registerEvent('register_set_errorconflict', 'onRegisterError');
        $this->registerEvent('register_set_errornotacceptable', 'onRegisterNotAcceptable');
        $this->registerEvent('register_get_errorserviceunavailable', 'onServiceUnavailable');
    }

    function display()
    {
        $host = $_GET['s'];
        
        $this->view->assign('getsubscriptionform', $this->call('ajaxGetForm', "'".$host."'"));
        $this->view->assign('host', $host);
    }

    function onForm($package)
    {
        $form = $package->content;

        $xtf = new \XMPPtoForm();
        if(!empty($form->x)){
            $ns = $form->x->getNamespaces();
            switch($ns['']) {
                case 'jabber:x:data' :
                    $formview = $this->tpl();
                    
                    $formh = $xtf->getHTML($form->x->asXML());
                    $formview->assign('submitdata', $this->call('ajaxRegister', "movim_form_to_json('data')"));

                    $formview->assign('formh', $formh);
                    $html = $formview->draw('_accountnext_form', true);

                    RPC::call('movim_fill', 'subscription_form', $html);
                    break;
                case 'jabber:x:oob' :
                    $oobview = $this->tpl();
                    $oobview->assign('url', (string)$form->x->url);
                    
                    $html = $oobview->draw('_accountnext_oob', true);
                    
                    RPC::call('movim_fill', 'subscription_form', $html);
                    RPC::call('remoteUnregister');
                    break;
            }
            
        } else{
            $formh = $xtf->getHTML($form->asXML());
        }
    }

    function onRegistered($packet)
    {
        $data = $packet->content;

        $view = $this->tpl();
        $view->assign('url', (string)$form->x->url);
        
        $html = $view->draw('_accountnext_registered', true);
        
        RPC::call('movim_fill', 'subscription_form', $html);
        RPC::call('setUsername', $data->username->value);
        RPC::call('remoteUnregister');
    }

    function onRegisterError($package)
    {
        $error = $package->content;
        RPC::call('movim_fill', 'subscription_error', '<div class="message error">'.$error.'</div>');
    }
    
    function onRegisterNotAcceptable()
    {
        RPC::call('movim_fill', 'subscription_error', '<div class="message error">'.$this->__('error.not_acceptable').'</div>');
    }
    
    function onServiceUnavailable()
    {
        RPC::call('movim_fill', 'subscription_form', '<div class="message error">'.$this->__('error.service_unavailable').'</div>');
        RPC::call('remoteUnregister');
    }

    function ajaxGetForm($host)
    {
        \Moxl\Stanza\Stream::init($host);
    }

    function ajaxRegister($form)
    {
        $s = new Set;
        $s->setData($form)->request();
    }
}
