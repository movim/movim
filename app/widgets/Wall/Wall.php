<?php

/**
 * @package Widgets
 *
 * @file Wall.php
 * This file is part of MOVIM.
 *
 * @brief The contact feed
 *
 * @author Jaussoin Timothée <edhelas_at_gmail_dot_com>
 *
 * @version 1.0
 * @date 30 september 2011
 *
 * Copyright (C)2010 MOVIM project
 *
 * See COPYING for licensing information.
 */

use Moxl\Xec\Action\Pubsub\GetItems;

class Wall extends WidgetCommon
{

    function load()
    {
        $this->addjs('wall.js');
        $this->registerEvent('postmicroblog', 'onStream');
        $this->registerEvent('stream', 'onStream');
        $this->registerEvent('comment', 'onComment');
        $this->registerEvent('nocomment', 'onNoComment');
        $this->registerEvent('nocommentstream', 'onNoCommentStream');
        $this->registerEvent('nostream', 'onNoStream');
        $this->registerEvent('nostreamautorized', 'onNoStreamAutorized');
    }
    
    function display() {
        $this->view->assign('refresh', $this->genCallAjax('ajaxWall', '"'.$_GET['f'].'"'));
    }
    
    function onNoStream() {
        $html = '<div style="padding: 1.5em; text-align: center;">Ain\'t Nobody Here But Us Chickens...</div>';
        RPC::call('movim_fill', 'wall', $html);
        RPC::call('hideWall');
        RPC::commit();
    }
    
    function onNoStreamAutorized() {
        $html = '<div style="padding: 1.5em; text-align: center;">I\'m sorry, Dave. I\'m afraid I can\'t do that.</div>';
        RPC::call('movim_fill', 'wall', $html);
        RPC::commit();
    }  
    
    function onStream($payload) {
        $html = $this->prepareFeed(-1, $payload['from']);

        RPC::call('movim_fill', stringToUri($payload['from'].$payload['node']), $html);
    }

    function prepareFeed($start, $from = false) {
        if(!$from && isset($_GET['f'])) {
            $from = $_GET['f'];
        } else {
            return '';
        }
        
        $pd = new \Modl\PostnDAO();
        $pl = $pd->getNode($from, 'urn:xmpp:microblog:0', $start+1, 10);
        
        $cd = new \Modl\ContactDAO();
        $c = $cd->getRosterItem($from);
        
        // We ask for the HTML of all the posts
        
        $htmlmessages = $this->preparePosts($pl);

        $next = $start + 10;
        
        $html = '';
        
        if(count($pl) > 0 && $htmlmessages != false) {
            $wallhead = $this->tpl();
            $wallhead->assign('start', $start);
            $wallhead->assign('from', $from);
            $wallhead->assign('posts', $htmlmessages);
            $wallhead->assign('pl', $pl);
            $wallhead->assign('map', $this->printMap($pl, $c));
            $wallhead->assign('refresh', $this->genCallAjax('ajaxWall', "'".$from."'"));
            $wallhead->assign('older', $this->genCallAjax('ajaxGetFeed', "'".$next."'", "'".$from."'"));
            $html = $wallhead->draw('_wall_head', true);
        }
        
        return $html;
    }
    
    function ajaxGetFeed($start, $from) {
        RPC::call('movim_append', 'wall', $this->prepareFeed($start, $from));
        RPC::commit();
    }

    function ajaxWall($jid) {
        $r = new GetItems;
        $r->setTo($jid)
          ->setNode('urn:xmpp:microblog:0')
          ->request();
    }
    
    function ajaxSubscribe($jid) {
        $this->xmpp->subscribeNode($jid);
    }
}

?>
