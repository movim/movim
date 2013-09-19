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

class NodeConfig extends WidgetBase
{

    function WidgetLoad()
    {
        $this->registerEvent('pubsubconfig', 'onConfigForm');
        $this->registerEvent('pubsubconfigsubmited', 'onGroupConfig');
        $this->registerEvent('deletionsuccess', 'onGroupDeleted');
        
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
                '.t("Return to %s's list of groups", $server).'
            </a><br /><br />';
            
        Notification::appendNotification(t('Group deleted'), 'success');
        RPC::call('movim_fill', 'handlingmessages', $html);
        RPC::commit();        
    }
    
    function onGroupConfig($stanza) { 
        Notification::appendNotification(t('Group configuration saved'), 'success');
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
                        class="button color green icon yes" 
                        style="float: right;"
                        onclick="
                            '.$submit.'
                            this.onclick=null;
                            this.style.display = \'none\'
                            "
                    >
                        '.t('Validate').'
                </a>
                <br />
                <br />
            </form>';
        
        RPC::call('movim_fill', 'groupconfiguration', $html);
        RPC::commit();
    }
    
    function ajaxGroupConfig($server, $node){
        $r = new moxl\PubsubGetConfig();
        $r->setTo($server)
          ->setNode($node)
          ->request();
    }
    
    function ajaxGroupDelete($server, $node){
        $nd = new modl\ItemDAO();
        $nd->deleteItem($server, $node);
        
        $r = new moxl\GroupDelete();
        $r->setTo($server)
          ->setNode($node)
          ->request();
    }
    
    function ajaxSubmitConfig($data, $server, $node){
        $r = new moxl\PubsubSetConfig();
        $r->setTo($server)
          ->setNode($node)
          ->setData($data)
          ->request();
    }
    /*
    function build()
    {

        echo '
            <div class="breadcrumb">
                <a href="'.Route::urlize('explore').'">
                    '.t('Explore').'
                </a>
                <a href="'.Route::urlize('server', $_GET['s']).'">
                    '.$_GET['s'].'
                </a>
                <a href="'.Route::urlize('node', array($_GET['s'], $_GET['n'])).'">
                    '.$_GET['n'].'
                </a>
                <a>'.t('Configuration').'</a>
            </div>';
        ?>

        <div class="tabelem" title="<?php echo t('Configuration'); ?>" id="groupconfig">
            <h1><?php echo t('Configuration'); ?></h1>
            
            <div id="groupconfiguration" class="paddedtop">
                <div id="handlingmessages"></div>
                <a 
                    class="button color green icon write" 
                    onclick="<?php echo $this->genCallAjax('ajaxGroupConfig', "'".$_GET['s']."'", "'".$_GET['n']."'"); ?> this.style.display = 'none'">
                    <?php echo t("Configure your group");?>
                </a>
                <a 
                    class="button color red icon no" 
                    onclick="<?php echo $this->genCallAjax('ajaxGroupDelete', "'".$_GET['s']."'", "'".$_GET['n']."'"); ?> this.style.display = 'none'">
                    <?php echo t("Delete this group");?>
                </a>
            </div>
        </div>
        <?php
    }
    */
}

?>
