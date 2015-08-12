<?php

/**
 * @package Widgets
 *
 * @file NodeAffiliations.php
 * This file is part of MOVIM.
 *
 * @brief A widget for retrieving your group's members
 *
 * @author Ho Christine <nodpounod@gmail.com>
 *
 * @version 1.0
 * @date 17 April 2013
 *
 * Copyright (C)2010 MOVIM project
 *
 * See COPYING for licensing information.
 */

use Moxl\Xec\Action\Pubsub\GetSubscriptions;
use Moxl\Xec\Action\Pubsub\SetSubscriptions;

class NodeSubscriptions extends WidgetBase
{
    function load() {
        $this->registerEvent('pubsubsubscriptions', 'onSubscriptionsList');
        $this->registerEvent('pubsubsubscriptionsssubmited', 'onSubmit');
    }
    
    function display() {
        $this->view->assign('pepfilter', !filter_var($_GET['s'], FILTER_VALIDATE_EMAIL));
        $this->view->assign('getsubscriptions', 
            $this->call('ajaxGetSubscriptions', 
                "'".$_GET['s']."'", 
                "'".$_GET['n']."'"));
    }
    
    function prepareList($list) { //0:data 1:server 2:node
        $subscription = array("none", "pending", "unconfigured", "subscribed");
        $html = '<form id="subscriptionsManaging">';

        foreach($list['subscriptions'] as $item){ //0:jid 1:affiliation 2:subid 
            $html .= '
                <div class="element">
                    <label for="'.$item['jid'].'_'.$item['subid'].'">
                        <a href="'.Route::urlize('friend', $item['jid']).'">'.$item['jid'].'</a>
                    </label>
                    <div class="select">
                        <select name="'.$item['jid'].'_'.$item['subid'].'">';
                        foreach($subscription as $status){
                            $status == $item['subscription'] ? $selected = "selected" : $selected = "";
                            $html .= '<option '.$selected.'>'.t($status).'</option>';
                        }
            $html .= '  </select>
                    </div>
                </div>';
        }
        
        $ok = $this->call('ajaxChangeSubscriptions', "'".$list['to']."'", "'".$list['node']."'", "movim_parse_form('subscriptionsManaging')");
        $html .= '
            <hr />
            <br />
            <a 
                class="button color green oppose" 
                onclick="'.$ok.'">
                <i class="fa fa-check"></i> '.__('button.validate').'
            </a></form><div class="clear"></div>';
        return $html;
    }
    
    function onSubmit($stanza) {
        Notification::append(null, $this->__('subscriptions.saved'));
        RPC::commit();        
    }
    
    function onSubscriptionsList($list) {
        $html = $this->prepareList($list);
        RPC::call('movim_fill', 'subscriptionslist', $html); 
        RPC::commit(); 
    }
    
    function ajaxChangeSubscriptions($server, $node, $data){
        $r = new SetSubscriptions;
        $r->setNode($node)
          ->setTo($server)
          ->setData($data)
          ->request();
    }
    
    function ajaxGetSubscriptions($server, $node){
        $r = new GetSubscriptions;
        $r->setTo($server)
          ->setNode($node)
          ->request();
    }
}

?>
