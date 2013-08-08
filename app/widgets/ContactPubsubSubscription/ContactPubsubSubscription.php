<?php

/**
 * @package Widgets
 *
 * @file ContactPubsubSubscription.php
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

class ContactPubsubSubscription extends WidgetBase
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
                $html .= '<li><a href="'.Route::urlize('node', array($item[1], $item[0])).'">'.$item[2].'</a></li>';
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
            <div style="position:relative;top:-1.5em;right:-1.5em;" class="protect red" title="<?php echo getFlagTitle('red'); ?>"></div>
            <a 
                class="button icon yes color green" 
                onclick="<?php echo $this->genCallAjax('ajaxGetGroupSubscribedList', "'".$_GET['f']."'"); ?> this.style.display = 'none'">
                <?php echo t("Get public groups");?>
            </a>
            <div id="publicgroups"></div>
        </div>
        <?php
    }
}

?>
