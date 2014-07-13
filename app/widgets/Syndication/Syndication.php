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
    function load()
    {
        ob_clean();

        $from = $_GET['f'];
        $node = $_GET['n'];
        
        $this->view->assign('from', $from);
        $this->view->assign('node', $node);
        
        $pd = new \modl\PostnDAO();
        
        if(isset($from) && isset($node)) {
            $messages = $pd->getPublic($from, $node);
            $this->view->assign('messages', $messages);
        }
        
        if(isset($messages[0])) {
            header("Content-Type: application/atom+xml; charset=UTF-8");
            // Title and logo
            // For a Pubsub feed
            if(isset($from) && isset($node) && $node != 'urn:xmpp:microblog:0') {
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
            $this->view->assign('uri',  htmlentities(Route::urlize('blog',array($from, $node))));
            $this->view->assign('link', '<link rel="self" href="'.htmlentities(Route::urlize('feed',array($from, $node))).'"/>');
            $this->view->assign('uuid', generateUUID($from.$node));
        }
    }
    
    function prepareTitle($title) {
        if($title == null)
            return '...';
        else
            return $this->prepareContent($title, true);     
    }
    
    function prepareContent($content, $title = false) {
        if($title)
            return cleanHTMLTags($content);
        else
            return trim(cleanHTMLTags(prepareString($content)));
    }

    function generateUUID($content) {
        return generateUUID(serialize($content));
    }

    function prepareUpdated($date) {
        return date('c', strtotime($date));
    }
}
