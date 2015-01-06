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

use Moxl\Xec\Action\Pubsub\GetAffiliations;
use Moxl\Xec\Action\Pubsub\SetAffiliations;

class NodeAffiliations extends WidgetBase
{
    function load()
    {
        $this->registerEvent('pubsubaffiliations', 'onGroupMemberList');
        $this->registerEvent('pubsubaffiliationssubmited', 'onSubmit');
    }
    
    function display() {
        $this->view->assign('pepfilter', !filter_var($_GET['s'], FILTER_VALIDATE_EMAIL));
        $this->view->assign('getaffiliations', 
            $this->call('ajaxGetGroupMemberList',
                "'".$_GET['s']."'", 
                "'".$_GET['n']."'"));
    }
    
    function prepareList($list) { //0:data 1:server 2:node
        $affiliation = array("owner", "member", "none");
        $html = '<form id="affiliationsManaging">';

        foreach($list[0] as $item){ //0:jid 1:affiliation 2:subid 
            $html .= '
                <div class="element">
                    <label for="'.$item[0].'_'.$item[2].'">
                        <a href="'.Route::urlize('friend', $item[0]).'">'.$item[0].'</a>
                    </label>
                    <div class="select">
                        <select name="'.$item[0].'_'.$item[2].'">';
                        foreach($affiliation as $status){
                            $status == $item[1] ? $selected = "selected" : $selected = "";
                            $html .= '<option '.$selected.'>'.t($status).'</option>';
                        }
            $html .= '  </select>
                    </div>
                </div>';
        }
        
        $ok = $this->call(
                'ajaxChangeAffiliation', 
                "'".$list[1]."'", 
                "'".$list[2]."'", 
                "movim_parse_form('affiliationsManaging')");
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
        Notification::append(null, $this->__('affiliations.saved'));
        RPC::commit();        
    }
    
    function onGroupMemberList($list) {
        $html = $this->prepareList($list);
        RPC::call('movim_fill', 'memberlist', $html); 
        RPC::commit(); 
    }
    
    function ajaxChangeAffiliation($server, $node, $data){
        $r = new SetAffiliations;
        $r->setNode($node)->setTo($server)->setData($data)
          ->request();
    }
    
    function ajaxGetGroupMemberList($server, $node){
        $r = new GetAffiliations;
        $r->setTo($server)->setNode($node)
        ->request();
    }
}
