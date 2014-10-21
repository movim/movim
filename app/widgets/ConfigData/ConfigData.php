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
    function load()
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
        RPC::call('movim_reload_this');
        RPC::commit();
    }
}
