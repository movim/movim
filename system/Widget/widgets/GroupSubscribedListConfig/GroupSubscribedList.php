<?php

/**
 * @package Widgets
 *
 * @file GroupConfig.php
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

class GroupSubscribedList extends WidgetBase
{

    function WidgetLoad()
    {
        $this->registerEvent('groupsubscribedlist', 'onGroupSubscribedList');
    }
    
    function prepareList($list) { 
        $html = '<ul class="list">';
        
        foreach($list as $item){
            $delete = $this->genCallAjax('ajaxDeleteFromGroupSubscribedList', "'".$item[0]."'", "'".$item[1]."'");
            $html .= '<li>'.$item[2].'<a onclick="'.$delete.'">'.t('Delete').'</a></li>';
        }
        
        $html .= '</ul>';
        return $html;
    }
    
    function onGroupSubscribedList($list) {
        $html = $this->prepareList($list);
        RPC::call('movim_fill', 'publicgroups', RPC::cdata($html)); 
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
		<div class="tabelem" title="<?php echo t('Public groups'); ?>" id="groupsubscribedlist">
            <a class="button tiny icon" onclick="<?php echo $this->genCallAjax('ajaxGetGroupSubscribedList'); ?>"><?php echo t("Get your public groups");?></a>
            <div id="publicgroups"></div>
        </div>
        <?php
    }
}

?>
