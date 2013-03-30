<?php

/**
 * @package Widgets
 *
 * @file GroupSubscribedListFromFriend.php
 * This file is part of MOVIM.
 *
 * @brief The Group configuration widget
 *
 * @author Ho Christine <nodpounod@gmail.com>
 *
 * @version 1.0
 * @date 24 March 2013
 *
 * Copyright (C)2010 MOVIM project
 *
 * See COPYING for licensing information.
 */

class GroupSubscribedListFromFriend extends WidgetBase
{

    function WidgetLoad()
    {
        $this->registerEvent('groupsubscribedlist', 'onGroupSubscribedList');
        $this->registerEvent('groupsubscribedlisterror', 'onGroupSubscribedListError');
    }
    
    function prepareList($list) { 
        if(is_array($list[0])){
            $html = '<ul class="list">';
            
            foreach($list as $item){
                $html .= '<li><a href="?q=node&s='.$item[1].'&n='.$item[0].'">'.$item[2].'</a></li>';
            }
            
            $html .= '</ul>';
            return $html;
        }
        
        Notification::appendNotification(t('No public groups found'), 'info');
    }
    
    function onGroupSubscribedList($list) {
        $html = $this->prepareList($list);
        RPC::call('movim_fill', 'publicgroups', $html); 
    }
    
    function onGroupSubscribedListError($error)
    {
        Notification::appendNotification($error, 'error');
    }
    
    function ajaxGetGroupSubscribedList($to){
        $r = new moxl\PubsubSubscriptionListGetFriends();
        $r->setTo($to)->request();
    }
    
	function build()
    {
        ?>
		<div class="tabelem padded" title="<?php echo t('Public groups'); ?>" id="groupsubscribedlistfromfriend">
            <a class="button tiny icon yes" onclick="<?php echo $this->genCallAjax('ajaxGetGroupSubscribedList', "'".$_GET['f']."'"); ?>"><?php echo t("Get public groups");?></a>
            <div id="publicgroups"></div>
        </div>
        <?php
    }
}

?>
