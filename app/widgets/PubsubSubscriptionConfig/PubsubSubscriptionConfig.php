<?php

/**
 * @package Widgets
 *
 * @file PubsubSubscriptionConfig.php
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

class PubsubSubscriptionConfig extends WidgetBase
{

    function WidgetLoad()
    {
        $this->registerEvent('groupsubscribedlist', 'onGroupSubscribedList');
        $this->registerEvent('groupremoved', 'onGroupRemoved');
    }
    
    function prepareList($list) { 
        if(isset($list) && is_array($list[0])){
            $html = '<ul class="list">';
            foreach($list as $item){
                $delete = $this->genCallAjax('ajaxDeleteFromGroupSubscribedList', "'".$item[0]."'", "'".$item[1]."'");
                $html .= '
                    <li id="group'.$item[0].'">
                        <a class="action" onclick="'.$delete.'">'.t('Delete').'</a>
                        <a href="'.Route::urlize('node', array($item[1],$item[0])).'">'.$item[2].'</a>
                    </li>';
            }
            $html .= '</ul>';
            return $html;
        }
        else return t('No public groups found');
    }
    
    function onGroupRemoved($node) {       
        RPC::call('movim_delete', 'group'.$node);
        Notification::appendNotification(t('%s has been removed from your public groups', $node), 'success');
        RPC::commit(); 
    }
    
    function onGroupSubscribedList($list) {
        $html = $this->prepareList($list);
        RPC::call('movim_fill', 'listconfig', $html); 
    }
    
    function ajaxDeleteFromGroupSubscribedList($node, $server){
        $r = new moxl\PubsubSubscriptionListRemove();
        $r->setNode($node)
          ->setTo($server)
          ->setFrom($this->user->getLogin())
          ->request();
    }
    
    function ajaxGetGroupSubscribedList(){
        $r = new moxl\PubsubSubscriptionListGet();
        $r->request();
    }
    
    function build()
    {
        ?>
        <div class="tabelem padded" title="<?php echo t('Public Groups'); ?>" id="groupsubscribedlistconfig">
            <div id="listconfig">
                <a 
                    class="button icon yes color green" 
                    onclick="<?php echo $this->genCallAjax('ajaxGetGroupSubscribedList'); ?> this.style.display = 'none';">
                    <?php echo t("Get your public groups");?>
                </a>
            </div>
        </div>
        <?php
    }
}

?>
