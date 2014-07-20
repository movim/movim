<?php

/**
 * @package Widgets
 *
 * @file Node.php
 * This file is part of MOVIM.
 *
 * @brief The items of a node
 *
 * @author Timothée    Jaussoin <edhelas_at_gmail_dot_com>
 *
 * @version 1.0
 * @date 20 October 2010
 *
 * Copyright (C)2010 MOVIM project
 *
 * See COPYING for licensing information.
 */

use Moxl\Xec\Action\Pubsub\GetItems;
use Moxl\Xec\Action\Pubsub\Subscribe;
use Moxl\Xec\Action\Pubsub\Unsubscribe;
use Moxl\Xec\Action\Pubsub\GetAffiliations;
use Moxl\Xec\Action\Pubsub\GetMetadata;
use Moxl\Xec\Action\Pubsub\GetSubscriptions;

class Node extends WidgetCommon
{
    private $role;
    private $_feedsize = 10;
    
    function load()
    {
        $this->registerEvent('post', 'onStream');
        $this->registerEvent('stream', 'onStream');
        $this->registerEvent('nostream', 'onStream');
        $this->registerEvent('pubsubaffiliations', 'onPubsubAffiliations');
        $this->registerEvent('pubsubsubscribed', 'onPubsubSubscribed');
        $this->registerEvent('pubsubmetadata', 'onPubsubMetadata');
        $this->registerEvent('pubsubsubscribederror', 'onPubsubSubscribedError');
        $this->registerEvent('pubsubunsubscribed', 'onPubsubUnsubscribed');
    }

    function display()
    {
        if(isset($_GET['s']) && isset($_GET['n'])) {
            $this->view->assign('server', $_GET['s']);
            $this->view->assign('node',   $_GET['n']);
            $this->view->assign('getaffiliations',  $this->genCallAjax('ajaxGetAffiliations', "'".$_GET['s']."'", "'".$_GET['n']."'"));
            $this->view->assign('getmetadata',      $this->genCallAjax('ajaxGetMetadata', "'".$_GET['s']."'", "'".$_GET['n']."'"));
            $this->view->assign('hash',             md5($_GET['s'].$_GET['n']));
            $this->view->assign('items',            $this->prepareNode($_GET['s'], $_GET['n']));
            $this->view->assign('metadata',         $this->prepareMetadata($_GET['s'], $_GET['n']));
            
            $nd = new modl\ItemDAO();
            $node = $nd->getItem($_GET['s'], $_GET['n']);
            
            if($node != null)
                $title = $node->getName();
            else
                $title = $groupid;

            $this->view->assign('title',          $title);
            
            $this->view->assign('formpublish', $this->prepareSubmitForm($_GET['s'], $_GET['n']));
        }
    }
    
    function onPubsubSubscribed($params)
    {        
        $html = $this->prepareNode($params[0], $params[1]);
        RPC::call('setBookmark');
        RPC::call('movim_fill', 'node', $html);
        RPC::call('movim_reload_this');
    }
    
    function onPubsubSubscribedError($params)
    {        
        $this->onPubsubSubscribed($params);
    }
    
    function onPubsubUnsubscribed($params)
    {
        $this->onPubsubSubscribed($params);
    }

    function onPubsubAffiliations($params) {
        foreach($params[0] as $r) {
            if($r[0] == $this->user->getLogin())
                $this->role = (string)$r[1];
        }

        if($this->searchSubscription($params[1], $params[2])
        && ($this->role == 'owner' || $this->role == 'publisher')) {
            RPC::call('movim_toggle_display', '#formpublish');
            RPC::call('movim_toggle_display', '#configbutton');
        }
    }

    function onPubsubMetadata($params) {
        $html = $this->prepareMetadata($params[0], $params[1]);
        RPC::call('movim_fill', 'metadata', $html);
    }
    
    function onStream($payload) {
        $html = $this->prepareNode($payload['from'], $payload['node']);

        if($html == '') 
            $html = t("Your feed cannot be loaded.");
        RPC::call('movim_fill', md5($payload['from'].$payload['node']), $html);
    }

    function ajaxGetItems($server, $node)
    {
        $pd = new \Modl\PostnDAO();
        $pd->deleteNode($server, $node);
    
        $r = new GetItems;
        $r->setTo($server)
          ->setNode($node)
          ->request();
    }
    
    function ajaxSubscribe($data, $server, $node)
    {
        $g = new Subscribe;
        $g->setTo($server)
          ->setNode($node)
          ->setFrom($this->user->getLogin())
          ->setData($data)
          ->request();
    }
    
    function ajaxUnsubscribe($server, $node)
    {
        $sd = new \Modl\SubscriptionDAO();

        foreach($sd->get($server, $node) as $s) {
            $g = new Unsubscribe;
            $g->setTo($server)
              ->setNode($node)
              ->setSubid($s->subid)
              ->setFrom($this->user->getLogin())
              ->request();
        }
    }
    
    function ajaxGetSubscriptions($server, $node)
    {
        $r = new GetSubscriptions;
        $r->setTo($server)
          ->setNode($node)
          ->setSync()
          ->request();
    }

    function ajaxGetAffiliations($server, $node){
        $r = new GetAffiliations;
        $r->setTo($server)->setNode($node)
          ->request();
    }
    
    function ajaxGetMetadata($server, $node){
        $r = new GetMetadata;
        $r->setTo($server)->setNode($node)
          ->request();
    }
    
    function prepareNode($serverid, $groupid) {
        $nodeview = $this->tpl();
        $nodeview->assign('serverid',       $serverid);
        $nodeview->assign('groupid',        $groupid);
        $nodeview->assign('subscribed',     $this->searchSubscription($serverid, $groupid));
        
        $nodeview->assign('role',           $this->role);
        
        $nodeview->assign('refresh',        $this->genCallAjax('ajaxGetItems', "'".$serverid."'", "'".$groupid."'"));
        $nodeview->assign('getsubscription',$this->genCallAjax('ajaxGetSubscriptions', "'".$serverid."'", "'".$groupid."'"));
        $nodeview->assign('subscribe',      $this->genCallAjax('ajaxSubscribe', "movim_parse_form('groupsubscribe')", "'".$serverid."'", "'".$groupid."'"));
        $nodeview->assign('unsubscribe',    $this->genCallAjax('ajaxUnsubscribe', "'".$serverid."'", "'".$groupid."'"));

        $nodeview->assign('submitform',     '');

        $nodeview->assign('posts',          $this->preparePostsNode($serverid, $groupid, -1));

        $html = $nodeview->draw('_node_content', true);
        
        return $html;
    }

    function prepareNext($start, $html = '', $posts, $function = 'ajaxGetPostsNode', $serverid, $groupid) {
        $next = $start + $this->_feedsize;
        
        $nexthtml = '';
            
        if(sizeof($posts) > $this->_feedsize-1 && $html != '') {
            $nexthtml = '
                <div class="block large">
                    <div
                        class="older"
                        onclick="'.$this->genCallAjax($function, "'".$serverid."'", "'".$groupid."'", "'".$next."'").'; this.parentNode.style.display = \'none\'">
                        <i class="fa fa-history"></i> '. __('post.older') .'
                    </div>
                </div>';
        }   

        return $nexthtml;
    }

    function prepareMetadata($server, $node) {
        $nd = new modl\ItemDAO();
        $node = $nd->getItem($server, $node);

        $metadataview = $this->tpl();
        if(isset($node->name))
            $metadataview->assign('title',       $node->name);
        else
            $metadataview->assign('title',       $node->node);
        $metadataview->assign('description', $node->description);
        $metadataview->assign('creation',    prepareDate(strtotime($node->created)));
        $metadataview->assign('creator',     $node->creator);

        return $metadataview->draw('_node_metadata', true);
    }
    
    function preparePostsNode($serverid, $groupid, $start) {
        $pd = new \Modl\PostnDAO();
        $pl = $pd->getNode($serverid, $groupid, $start+1, $this->_feedsize);

        if(isset($pl)) {
            $html = $this->preparePosts($pl);
        } else {
            $view = $this->tpl();
            $html = $view->draw('_node_empty', true);
        }

        $html .= $this->prepareNext($start, $html, $pl, 'ajaxGetPostsNode', $serverid, $groupid);
        
        return $html;
    }

    function ajaxGetPostsNode($serverid, $groupid, $start) {
        $html = $this->preparePostsNode($serverid, $groupid, $start);        
        RPC::call('movim_append', md5($serverid.$groupid), $html);
        RPC::commit();
    }
    
    function searchSubscribed($server, $node) {
        $c = Cache::c('bookmark');
        foreach($c as $bookmark) {
            if(
                $bookmark['type'] == 'subscription' && 
                $bookmark['server'] == $server &&
                $bookmark['node'] == $node) {
                return true;
            }
        }
        return false;
    }
    
    function searchSubscription($server, $node) {
        $sd = new \Modl\SubscriptionDAO();
        $subs = $sd->get($server, $node);
        
        if($subs != null)
            foreach($subs as $s) {
                if($s->subscription == 'subscribed')
                    return true;
            }
        return false;
    }
}
