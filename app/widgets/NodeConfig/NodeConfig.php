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
 * @date 12 March 2013
 *
 * Copyright (C)2010 MOVIM project
 *
 * See COPYING for licensing information.
 */

use Moxl\Xec\Action\Pubsub\GetConfig;
use Moxl\Xec\Action\Pubsub\SetConfig;
use Moxl\Xec\Action\Group\Delete;

class NodeConfig extends WidgetBase
{

    function load()
    {
        $this->registerEvent('pubsubconfig', 'onConfigForm');
        $this->registerEvent('pubsubconfigsubmited', 'onGroupConfig');
        $this->registerEvent('deletionsuccess', 'onGroupDeleted');
    }

    function display()
    {
        if(isset($_GET['s']) && isset($_GET['n'])) {
            $nd = new modl\ItemDAO();
            $node = $nd->getItem($_GET['s'], $_GET['n']);
            
            if($node != null)
                $title = $node->getName();
            else
                $title = $groupid;
            
            $this->view->assign('server', $_GET['s']);
            $this->view->assign('node', $_GET['n']);
            $this->view->assign('name', $title);
            $this->view->assign('group_config', $this->genCallAjax('ajaxGroupConfig', "'".$_GET['s']."'", "'".$_GET['n']."'"));
            $this->view->assign('group_delete', $this->genCallAjax('ajaxGroupDelete', "'".$_GET['s']."'", "'".$_GET['n']."'"));
        }
    }
    
    function onGroupDeleted($server) {
        $html = '
            <a href="'.Route::urlize('server', $server).'">
                '.$this->__('group.delete_return', $server).'
            </a><br /><br />';
            
        Notification::appendNotification(t('Group deleted'), 'success');
        RPC::call('movim_fill', 'handlingmessages', $html);
        RPC::commit();        
    }
    
    function onGroupConfig($stanza) { 
        Notification::appendNotification($this->__('group.config_saved'), 'success');
        RPC::commit();        
    }
    
    function onConfigForm($form) {
        $submit = $this->genCallAjax('ajaxSubmitConfig', "movim_parse_form('config')", "'".$form[1]."'", "'".$form[2]."'");
        $html = '
            <form name="config">'.
                $form[0].
                '
                <hr /><br />
                <a
                        class="button color green oppose" 
                        onclick="
                            '.$submit.'
                            this.onclick=null;
                            this.style.display = \'none\'
                            "
                    >
                        <i class="fa fa-check"></i> '.__('button.validate').'
                </a>
                <br />
                <br />
            </form>';
        
        RPC::call('movim_fill', 'groupconfiguration', $html);
        RPC::commit();
    }
    
    function ajaxGroupConfig($server, $node){
        $r = new GetConfig;
        $r->setTo($server)
          ->setNode($node)
          ->request();
    }
    
    function ajaxGroupDelete($server, $node){
        $nd = new \Modl\ItemDAO();
        $nd->deleteItem($server, $node);
        
        $r = new Delete;
        $r->setTo($server)
          ->setNode($node)
          ->request();
    }
    
    function ajaxSubmitConfig($data, $server, $node){
        $r = new SetConfig;
        $r->setTo($server)
          ->setNode($node)
          ->setData($data)
          ->request();
    }
}

?>
