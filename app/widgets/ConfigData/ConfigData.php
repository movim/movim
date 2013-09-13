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
        $this->addcss('configdata.css');
        
        $cd = new \modl\ContactDAO();
        $stats = $cd->getStatistics();

        $pd = new \modl\PostnDAO();
        $pstats = array_slice($pd->getStatistics(), 0, 10);

        $md = new \modl\MessageDAO();
        $mstats = array_slice($md->getStatistics(), 0, 10);

        $this->view->assign('stats',            $stats[0]);
        $this->view->assign('pstats',           $pstats);
        $this->view->assign('mstats',           $mstats);
        $this->view->assign('clearrosterlink',  $this->genCallAjax('ajaxClearRosterLink'));
        $this->view->assign('clearmessage',     $this->genCallAjax('ajaxClearMessage'));
        $this->view->assign('clearpost',        $this->genCallAjax('ajaxClearPost'));
    }
    
    function formatDate($month, $year) {
        return date('M', mktime(0, 0, 0, $month, 1, $year)); 
    }
    
    function formatHeight($height) {
        return log10($height)*20;
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
    
    /*function prepareConfigData() {
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
                        <label for="name">'.t('Post'). ' - '.$stats['post'].'</label><br />
                        <a 
                            type="button" 
                            name="email" 
                            class="button icon color red back"
                            onclick="'.$clearpost.'">'.t('Clear').'</a>
                    </div>
                    <div class="element thin">
                        <label for="name">'.t('Messages'). ' - '.$stats['message'].'</label><br />
                        <a 
                            type="button" 
                            name="email" 
                            class="button icon color red back"
                            onclick="'.$clearmessage.'">'.t('Clear').'</a>
                    </div>
                    <div class="element thin">
                        <label for="name">'.t('Contacts'). ' - '.$stats['rosterlink'].'</label><br />
                        <a 
                            type="button" 
                            class="button icon color red back"
                            onclick="'.$clearrosterlink.'">'.t('Clear').'</a>
                    </div>
                </fieldset>
            </form>';
        return $html;
    }*/
    
    /*function build()
    {
        ?>
        <div class="tabelem padded" title="<?php echo t('Data'); ?>" id="configdata" >
            <?php echo $this->prepareConfigData(); ?>
        </div>
        <?php
    }
    */
}
