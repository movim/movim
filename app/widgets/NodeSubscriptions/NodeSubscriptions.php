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

class NodeSubscriptions extends WidgetBase
{

    function WidgetLoad()
    {
        $this->registerEvent('pubsubsubscriptions', 'onSubscriptionsList');
        $this->registerEvent('pubsubsubscriptionsssubmited', 'onSubmit');
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
        
        $ok = $this->genCallAjax('ajaxChangeSubscriptions', "'".$list['to']."'", "'".$list['node']."'", "movim_parse_form('subscriptionsManaging')");
        $html .= '
            <hr />
            <br />
            <a 
                class="button color green icon yes" 
                style="float: right;"
                onclick="'.$ok.'">
                '.t('Validate').'
            </a></form>';
        return $html;
    }
    
    function onSubmit($stanza) {
        Notification::appendNotification(t('Subscriptions saved'), 'success');
        RPC::commit();        
    }
    
    function onSubscriptionsList($list) {
        $html = $this->prepareList($list);
        RPC::call('movim_fill', 'subscriptionslist', $html); 
        RPC::commit(); 
    }
    
    function ajaxChangeSubscriptions($server, $node, $data){
        $r = new moxl\PubsubSetSubscriptions();
        $r->setNode($node)
          ->setTo($server)
          ->setData($data)
          ->request();
    }
    
    function ajaxGetSubscriptions($server, $node){
        $r = new moxl\PubsubGetSubscriptions();
        $r->setTo($server)
          ->setNode($node)
          ->request();
    }
    
    function build()
    {
        // A little filter to hide the widget if we load a PEP node
        if(!filter_var($_GET['s'], FILTER_VALIDATE_EMAIL)) {
        ?>
        <div id="subscriptions" class="tabelem" title="<?php echo t('Manage your subscriptions'); ?>">
            <h1><?php echo t('Manage the subscriptions'); ?></h1>
            <div class="posthead">
                <a 
                    class="button icon users color green" 
                    onclick="<?php echo $this->genCallAjax('ajaxGetSubscriptions', "'".$_GET['s']."'", "'".$_GET['n']."'"); ?> this.parentNode.style.display = 'none'">
                        <?php echo t("Get the subscriptions");?>
                </a>
            </div>
            
            <div id="subscriptionslist" class="paddedtop"></div>
        </div>
        <?php
        }
    }
}

?>
