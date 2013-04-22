<?php

/**
 * @package Widgets
 *
 * @file GroupMemberList.php
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

class GroupMemberList extends WidgetBase
{

    function WidgetLoad()
    {
        $this->registerEvent('groupmemberlist', 'onGroupMemberList');
    }
    
    function prepareList($list) { //0:data 1:server 2:node
        $affiliation = array("owner", "member", "none");
        $html = '<form id="affiliationsManaging"><ul class="list">';
        
        foreach($list[0] as $item){ //0:jid 1:affiliation 2:subid 
            $html .= '
                <li> <a href="?q=friend&f='.$item[0].'" style="clear:both;">'.$item[0].'</a>
                    <div class="element"><select name="'.$item[0].'_'.$item[2].'">';
                        foreach($affiliation as $status){
                            $status == $item[1] ? $selected = "selected" : $selected = "";
                            $html .= '<option '.$selected.'>'.t($status).'</option>';
                        }
                    $html .= '</select></div>   
                </li>';
        }
        $ok = $this->genCallAjax('ajaxChangeAffiliation', "'".$list[1]."'", "'".$list[2]."'", "movim_parse_form('affiliationsManaging')");
        $html .= '</ul>
                <a 
                    class="button tiny icon" 
                    onclick="'.$ok.'">
                    '.t("Ok").'
                </a></form>';
        return $html;
    }
    
    function onGroupMemberList($list) {
        $html = $this->prepareList($list);
        RPC::call('movim_fill', 'memberlist', $html); 
		RPC::commit(); 
    }
    
    function ajaxChangeAffiliation($server, $node, $data){
        $r = new moxl\GroupSetMemberListAffiliation();
        $r->setNode($node)->setTo($server)->setData($data)
          ->request();
    }
    
    function ajaxGetGroupMemberList($server, $node){
        $r = new moxl\GroupGetMemberList();
        $r->setTo($server)->setNode($node)
        ->request();
    }
    
	function build()
    {
        ?>
		<div class="tabelem" title="<?php echo t('Manage your members'); ?>" id="groupmemberlist">
            <div class="posthead">
                <a 
                    class="button tiny icon" 
                    onclick="<?php echo $this->genCallAjax('ajaxGetGroupMemberList', "'".$_GET['s']."'", "'".$_GET['n']."'"); ?>">
                        <?php echo t("Get the members");?>
                </a>
            </div>
            
            <div id="memberlist" class="padded"></div>
        </div>
        <?php
    }
}

?>
