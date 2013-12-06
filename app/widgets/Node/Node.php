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

class Node extends WidgetCommon
{
    private $role;
    private $_feedsize = 10;
    
    function WidgetLoad()
    {
        $this->registerEvent('stream', 'onStream');
        $this->registerEvent('nostream', 'onStream');
        $this->registerEvent('pubsubaffiliations', 'onPubsubAffiliations');
        $this->registerEvent('pubsubsubscribed', 'onPubsubSubscribed');
        $this->registerEvent('pubsubmetadata', 'onPubsubMetadata');
        $this->registerEvent('pubsubsubscribederror', 'onPubsubSubscribedError');
        $this->registerEvent('pubsubunsubscribed', 'onPubsubUnsubscribed');

        $this->view->assign('server', $_GET['s']);
        $this->view->assign('node',   $_GET['n']);
        $this->view->assign('getaffiliations', $this->genCallAjax('ajaxGetAffiliations', "'".$_GET['s']."'", "'".$_GET['n']."'"));
        $this->view->assign('getmetadata', $this->genCallAjax('ajaxGetMetadata', "'".$_GET['s']."'", "'".$_GET['n']."'"));
        $this->view->assign('hash', md5($_GET['s'].$_GET['n']));
        $this->view->assign('items', $this->prepareNode($_GET['s'], $_GET['n']));
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
                $this->role = $r[1];
        }

        $html = $this->prepareNode($params[1], $params[2]);
        RPC::call('movim_fill', md5($params[1].$params[2]), $html);
    }

    function onPubsubMetadata($params) {
        // The URL add form
        $metadataview = $this->tpl();
        $metadataview->assign('title',       $params[0]['title']);
        $metadataview->assign('description', $params[0]['description']);
        $metadataview->assign('creation', prepareDate(strtotime($params[0]['creation_date'])));
        $metadataview->assign('creator',     $params[0]['creator']);

        $html = $metadataview->draw('_node_metadata', true);
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
        $r = new moxl\PubsubGetItems();
        $r->setTo($server)
          ->setNode($node)
          ->request();
    }
    
    function ajaxSubscribe($data, $server, $node)
    {
        $g = new moxl\PusubSubscribe();
        $g->setTo($server)
          ->setNode($node)
          ->setFrom($this->user->getLogin())
          ->setData($data)
          ->request();
    }
    
    function ajaxUnsubscribe($server, $node)
    {
        $sd = new \modl\SubscriptionDAO();

        foreach($sd->get($server, $node) as $s) {
            $g = new moxl\PubsubUnsubscribe();
            $g->setTo($server)
              ->setNode($node)
              ->setSubid($s->subid)
              ->setFrom($this->user->getLogin())
              ->request();
        }
    }
    
    function ajaxGetSubscriptions($server, $node)
    {
        $r = new moxl\PubsubGetSubscriptions();
        $r->setTo($server)
          ->setNode($node)
          ->setSync()
          ->request();
    }

    function ajaxGetAffiliations($server, $node){
        $r = new moxl\PubsubGetAffiliations();
        $r->setTo($server)->setNode($node)
          ->request();
    }
    
    function ajaxGetMetadata($server, $node){
        $r = new moxl\PubsubGetMetadata();
        $r->setTo($server)->setNode($node)
          ->request();
    }
    
    function prepareNode($serverid, $groupid) {
        $nd = new modl\ItemDAO();
        $node = $nd->getItem($serverid, $groupid);
        
        if($node != null)
            $title = $node->getName();
        else
            $title = $groupid;

        $nodeview = $this->tpl();
        $nodeview->assign('title',          $title);
        $nodeview->assign('serverid',       $serverid);
        $nodeview->assign('groupid',        $groupid);
        $nodeview->assign('subscribed',         $this->searchSubscription($serverid, $groupid));
        
        $nodeview->assign('role',           $this->role);
        
        $nodeview->assign('refresh',        $this->genCallAjax('ajaxGetItems', "'".$serverid."'", "'".$groupid."'"));
        $nodeview->assign('getsubscription',$this->genCallAjax('ajaxGetSubscriptions', "'".$serverid."'", "'".$groupid."'"));
        $nodeview->assign('subscribe',      $this->genCallAjax('ajaxSubscribe', "movim_parse_form('groupsubscribe')", "'".$serverid."'", "'".$groupid."'"));
        $nodeview->assign('unsubscribe',    $this->genCallAjax('ajaxUnsubscribe', "'".$serverid."'", "'".$groupid."'"));
        
        if($this->searchSubscription($serverid, $groupid)
        && ($this->role == 'owner' || $this->role == 'publisher'))
            $submitform = $this->prepareSubmitForm($serverid, $groupid);
        else
            $submitform = '';

        $nodeview->assign('submitform',     $submitform);

        $nodeview->assign('posts',           $this->preparePostsNode($serverid, $groupid, -1));

        $html = $nodeview->draw('_node_content', true);
        
        return $html;
    }

    function prepareNext($start, $html = '', $posts, $function = 'ajaxGetPostsNode', $serverid, $groupid) {
        $next = $start + $this->_feedsize;
        
        $nexthtml = '';
            
        if(sizeof($posts) > $this->_feedsize-1 && $html != '') {
            $nexthtml = '
                <div class="post">
                    <div 
                        class="older" 
                        onclick="'.$this->genCallAjax($function, "'".$serverid."'", "'".$groupid."'", "'".$next."'").'; this.parentNode.style.display = \'none\'">'.
                            t('Get older posts').'
                    </div>
                </div>';
        }   

        return $nexthtml;
    }
    
    function preparePostsNode($serverid, $groupid, $start) {
        $pd = new \modl\PostnDAO();
        $pl = $pd->getNode($serverid, $groupid, $start+1, $this->_feedsize);

        $html = $this->preparePosts($pl);

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
        $sd = new \modl\SubscriptionDAO();
        $subs = $sd->get($server, $node);
        
        if($subs != null)
            foreach($subs as $s) {
                if($s->subscription == 'subscribed')
                    return true;
            }
        return false;
    }
}
