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

class NodeAffiliations extends WidgetBase
{

    function WidgetLoad()
    {
        $this->registerEvent('pubsubaffiliations', 'onGroupMemberList');
        $this->registerEvent('pubsubaffiliationssubmited', 'onSubmit');
        $this->registerEvent('pubsubaffiliationserror', 'onGroupMemberListError');
    }
    
    function prepareList($list) { //0:data 1:server 2:node
        $affiliation = array("owner", "member", "none");
        $html = '<form id="affiliationsManaging">';

        foreach($list[0] as $item){ //0:jid 1:affiliation 2:subid 
            $html .= '
                <div class="element">
                    <label for="'.$item[0].'_'.$item[2].'"><a href="?q=friend&f='.$item[0].'">'.$item[0].'</a></label>
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
        
        $ok = $this->genCallAjax('ajaxChangeAffiliation', "'".$list[1]."'", "'".$list[2]."'", "movim_parse_form('affiliationsManaging')");
        $html .= '
            <hr />
            <br />
            <a 
                class="button icon yes" 
                style="float: right;"
                onclick="'.$ok.'">
                '.t('Validate').'
            </a></form>';
        return $html;
    }
    
    function onSubmit($stanza) {
        Notification::appendNotification(t('Affiliations saved'), 'success');
        RPC::commit();        
    }
    
    function onGroupMemberList($list) {
        $html = $this->prepareList($list);
        RPC::call('movim_fill', 'memberlist', $html); 
		RPC::commit(); 
    }
    
    function onGroupMemberListError($error) {        
        Notification::appendNotification(t('Error').' : '.$error, 'error');
        RPC::commit();
    }
    
    function ajaxChangeAffiliation($server, $node, $data){
        $r = new moxl\PubsubSetAffiliations();
        $r->setNode($node)->setTo($server)->setData($data)
          ->request();
    }
    
    function ajaxGetGroupMemberList($server, $node){
        $r = new moxl\PubsubGetAffiliations();
        $r->setTo($server)->setNode($node)
        ->request();
    }
    
	function build()
    {
        ?>
		<div class="tabelem" title="<?php echo t('Manage your members'); ?>" id="groupmemberlist">
            <div class="posthead">
                <a 
                    class="button icon submit" 
                    onclick="<?php echo $this->genCallAjax('ajaxGetGroupMemberList', "'".$_GET['s']."'", "'".$_GET['n']."'"); ?> this.parentNode.style.display = 'none'">
                        <?php echo t("Get the members");?>
                </a>
            </div>
            
            <div id="memberlist" class="padded"></div>
        </div>
        <?php
    }
}

?>
