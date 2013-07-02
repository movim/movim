<?php

/**
 * @package Widgets
 *
 * @file Syndication.php
 * This file is part of MOVIM.
 *
 * @brief Create a RSS feed from user posts
 *
 * @author Jaussoin Timothée <edhelas@gmail.com>
 *
 * @version 1.0
 *
 * Copyright (C)2010 MOVIM project
 *
 * See COPYING for licensing information.
 */

class Syndication extends WidgetBase
{
    function WidgetLoad()
    {
        ob_clean();
        header("Content-Type: application/atom+xml; charset=UTF-8");

        $from = $_GET['f'];
        $node = $_GET['n'];
        
        $this->view->assign('from', $from);
        $this->view->assign('node', $node);
        
        $pd = new \modl\PostnDAO();
        $messages = $pd->getPublic($from, $node);
        $this->view->assign('messages', $messages);
        
        if(isset($messages[0])) {
            // Title and logo
            // For a Pubsub feed
            if(isset($from) && isset($node) && $node != '') {
                $pd = new \modl\NodeDAO();
                $n = $pd->getNode($from, $node);
                if(isset($n->title))
                    $this->view->assign('title', $n->title);
                else
                    $this->view->assign('title', $n->nodeid);
            // Fir a simple contact
            } else {
                $this->view->assign('title', t("%s's feed",$messages[0]->getContact()->getTrueName()));
                $this->view->assign('logo', $messages[0]->getContact()->getPhoto('l'));
            }
            
            $this->view->assign('date', date('c'));
            $this->view->assign('name', $messages[0]->getContact()->getTrueName());
            $this->view->assign('uri',  Route::urlize('blog',array($from, $node)));
            $this->view->assign('link', Route::urlize('feed',array($from, $node)));
        }
    }
    
    function prepareTitle($title) {
        if($title == null)
            return trim(substr(strip_tags($title), 0, 40)).'...';
        else
            return $this->prepareContent($title);     
    }
    
    function prepareContent($content) {
        return prepareString($content);
    }
    
    function prepareUpdated($date) {
        return date('c', strtotime($date));
    }
}
