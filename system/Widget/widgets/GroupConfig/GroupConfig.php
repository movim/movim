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

class GroupConfig extends WidgetBase
{

    function WidgetLoad()
    {
        $this->registerEvent('configform', 'onConfigForm');
        $this->registerEvent('groupconfigsubmited', 'onGroupConfig');
        $this->registerEvent('groupconfigerror', 'onGroupConfigError');
    }
    
    function onGroupConfig($stanza) {
        $html = '<div class="message success">'.t('Group configuration saved').'</div>';
        
        RPC::call('movim_append', 'groupconfig', $html);
        RPC::commit();        
    }
    
    function onGroupConfigError($error) {
        $html = '<div class="message error">'.t('Error').' : '.$error.'</div>';
        
        RPC::call('movim_append', 'groupconfig', $html);
        RPC::commit();
    }
    
    function onConfigForm($form) {
        $submit = $this->genCallAjax('ajaxSubmitConfig', "movim_parse_form('config')", "'".$form[1]."'", "'".$form[2]."'");
        $html = '<form name="config">'.
                    $form[0].
                    '
                    <hr /><br />
                    <a
                            class="button icon yes" 
                            style="float: right;"
                            onclick="'.$submit.'"
                        >
                            '.t('Validate').'
                    </a>
                    <br />
                    <br />
                </form>';
        
        RPC::call('movim_fill', 'groupconfig', $html);
        RPC::commit();
    }
    
    function ajaxGroupConfig($server, $node){
        $r = new moxl\GroupGetConfigForm();
        $r->setTo($server)->setNode($node)
          ->request();
    }
    
    function ajaxSubmitConfig($data, $server, $node){
        //unset($data['pubsub#max_items']);
        $r = new moxl\GroupSetConfig();
        $r->setTo($server)->setNode($node)->setData($data)
          ->request();
    }
    
	function build()
    {
        ?>
		<div class="tabelem padded" title="<?php echo t('Configuration'); ?>" id="groupconfig">
            <a class="button tiny icon" onclick="<?php echo $this->genCallAjax('ajaxGroupConfig', "'".$_GET['s']."'", "'".$_GET['n']."'"); ?>"><?php echo t("Configure your group");?></a>
        </div>
        <?php
    }
}

?>
