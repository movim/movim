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
    
    function prepareList($list) { 
        $affiliation = array("owner", "member", "none");
        $html = '<ul class="list">';
        
        foreach($list as $item){
            $html .= '
                <li> '.$item[0].'
                    <select>';
                        foreach($affiliation as $status){
                            $affiliation[$i] == $item[1] ? $selected = "selected" : $selected = "";
                            $html .= '<option '.$selected.'>'.t($affiliation[$i]).'</option>';
                        }
                    $html .= '</select>    
                </li>';
        }
        
        $html .= '</ul>';
        return $html;
    }
    
    function onGroupMemberList($list) {
        $html = $this->prepareList($list);
        RPC::call('movim_fill', 'memberlist', $html); 
    }
    
    /*function ajaxDeleteFromMemberList($node, $server){
        $r = new moxl\PubsubSubscriptionListRemove();
        $r->setNode($node)
          ->setTo($server)
          ->setFrom($this->user->getLogin())
          ->request();
    }*/
    
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
