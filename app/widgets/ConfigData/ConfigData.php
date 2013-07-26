<?php

/**
 * @package Widgets
 *
 * @file Wall.php
 * This file is part of MOVIM.
 *
 * @brief The configuration form
 *
 * @author Timothée Jaussoin <edhelas_at_gmail_dot_com>
 *
 * @version 1.0
 * @date 28 October 2010
 *
 * Copyright (C)2010 MOVIM project
 *
 * See COPYING for licensing information.
 */

class ConfigData extends WidgetBase
{
    function WidgetLoad()
    {
    }
    
    function ajaxClearRosterLink() {
        $rd = new \modl\RosterLinkDAO();
        $rd->clearRosterLink();
        
        $this->refresh();
    }
    
    function ajaxClearMessage() {
        $md = new \modl\MessageDAO();
        $md->clearMessage();
        
        $this->refresh();
    }
    
    function ajaxClearPost() {
        $pd = new \modl\PostnDAO();
        $pd->clearPost();
        
        $this->refresh();
    }
    
    function refresh() {
        $html = $this->prepareConfigData();
        RPC::call('movim_fill', 'configdata', $html);
        RPC::commit();
    }
    
    function prepareConfigData() {
        $cd = new \modl\ContactDAO();
        $stats = $cd->getStatistics();

        $clearrosterlink    = $this->genCallAjax('ajaxClearRosterLink');
        $clearmessage       = $this->genCallAjax('ajaxClearMessage');
        $clearpost          = $this->genCallAjax('ajaxClearPost');
        
        $html = '
            <form enctype="multipart/form-data" method="post" action="index.php" name="general">
                <fieldset>
                    <legend>'.t('Cache').'</legend>
                    <div class="clear"></div>
                    <div class="element thin">
                        <label for="name">'.t('Post'). ' - '.$stats['Post'].'</label><br />
                        <a 
                            type="button" 
                            name="email" 
                            class="button icon color red back"
                            onclick="'.$clearpost.'">'.t('Clear').'</a>
                    </div>
                    <div class="element thin">
                        <label for="name">'.t('Messages'). ' - '.$stats['Message'].'</label><br />
                        <a 
                            type="button" 
                            name="email" 
                            class="button icon color red back"
                            onclick="'.$clearmessage.'">'.t('Clear').'</a>
                    </div>
                    <div class="element thin">
                        <label for="name">'.t('Contacts'). ' - '.$stats['RosterLink'].'</label><br />
                        <a 
                            type="button" 
                            class="button icon color red back"
                            onclick="'.$clearrosterlink.'">'.t('Clear').'</a>
                    </div>
                </fieldset>
            </form>';
        return $html;
    }
    
    function build()
    {
        ?>
        <div class="tabelem padded" title="<?php echo t('Data'); ?>" id="configdata" >
            <?php echo $this->prepareConfigData(); ?>
        </div>
        <?php
    }
}
