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
        $this->registerEvent('pubsubconfigerror', 'onGroupConfigError');
        $this->registerEvent('deletionsuccess', 'onGroupDeleted');
    }
    
    function onGroupDeleted($server) {
        $html = '
            <a href="?q=server&s='.$server.'">
                '.t("Return to %s's list of groups", $server).'
            </a>';
            
        Notification::appendNotification(t('Group deleted'), 'success');
        RPC::call('movim_fill', 'handlingmessages', $html);
        RPC::commit();        
    }
    
    function onGroupConfig($stanza) {        
        Notification::appendNotification(t('Group configuration saved'), 'success');
        RPC::commit();        
    }
    
    function onGroupConfigError($error) {
        Notification::appendNotification(t('Error').' : '.$error, 'error');
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
                        class="button icon yes" 
                        style="float: right;"
                        onclick="'.$submit.' this.onclick=null;"
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
    
	function build()
    {
        ?>
        
		<div class="tabelem" title="<?php echo t('Configuration'); ?>" id="groupconfig">
            <h1><?php echo t('Configuration'); ?></h1>
            
            <div id="groupconfiguration" class="paddedtop">
                <div id="handlingmessages"></div>
                <a 
                    class="button icon submit" 
                    onclick="<?php echo $this->genCallAjax('ajaxGroupConfig', "'".$_GET['s']."'", "'".$_GET['n']."'"); ?> this.style.display = 'none'">
                    <?php echo t("Configure your group");?>
                </a>
                <a 
                    class="button icon" 
                    onclick="<?php echo $this->genCallAjax('ajaxGroupDelete', "'".$_GET['s']."'", "'".$_GET['n']."'"); ?> this.style.display = 'none'">
                    <?php echo t("Delete this group");?>
                </a>
            </div>
        </div>
        <?php
    }
}

?>
