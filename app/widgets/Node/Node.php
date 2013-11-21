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
        
        if($this->searchSubscription($serverid, $groupid))
            $button = '
                <a
                    href="#" 
                    class="button color icon back"
                    onclick="movim_toggle_display(\'#groupunsubscribe\')">
                    '.t('Unsubscribe').'
                </a>';
        else
            $button = '
                <a 
                    href="#" 
                    class="button color green icon next"
                    onclick="movim_toggle_display(\'#groupsubscribe\')">
                    '.t('Subscribe').'
                </a>';
        
        $html = '
            <div class="breadcrumb">
                <a href="'.Route::urlize('explore').'">
                    '.t('Explore').'
                </a>
                <a href="'.Route::urlize('server', $serverid).'">
                    '.$serverid.'
                </a>
                <a href="'.Route::urlize('node', array($serverid, $groupid)).'">
                    '.$title.'
                </a>
                <a>'.t('Posts').'</a>
            </div>
            <div class="clear"></div>
            <div class="posthead">
                '.$button.'
                <a 
                    class="button color icon blog merged left" 
                    href="'.Route::urlize('blog',array($serverid,$groupid)).'"
                    target="_blank"
                >
                    '.t('Blog').'
                </a><a 
                    class="button color orange icon alone feed merged right" 
                    href="'.Route::urlize('feed',array($serverid,$groupid)).'"
                    target="_blank"
                ></a>
                <a
                    href="#"
                    onclick="'.$this->genCallAjax('ajaxGetItems', "'".$serverid."'", "'".$groupid."'").'
                    this.className=\'button icon color alone orange loading\'; this.onclick=null;"
                    class="button color blue icon alone refresh">
                    
                </a>
                
                <a 
                    class="button color icon yes"
                    onclick="
                        '.$this->genCallAjax('ajaxGetSubscriptions', "'".$serverid."'", "'".$groupid."'").'"
                >'.t('Get Subscription').'</a>';

        if($this->role == 'owner') {
            $html .= '
                <a 
                    class="button color icon user"
                    style="float: right;"
                    href="'.Route::urlize('nodeconfig', array($serverid,$groupid)).'"
                >'.t('Configuration').'</a>';
        }

        $html .= '
            </div>

            <div class="metadata" id="metadata">

            </div>
            
            <div class="popup" id="groupsubscribe">
                <form name="groupsubscribe">
                    <fieldset>
                        <legend>'.t('Subscribe').'</legend>
                        <div class="element">
                            <label>'.t('Make your membership to this group public to your friends').'</label>                            
                            <div class="checkbox">
                                <input type="checkbox" name="listgroup" id="listgroup"/>
                                <label for="listgroup"></label>
                            </div>
                        </div>
                        <div class="element">
                            <label for="grouptitle">'.t('Give a nickname to this group if you want').'</label>
                            <input type="text" name="title" value="'.$groupid.'" id="grouptitle"/>
                        </div>
                    </fieldset>
                    <div class="menu">
                        <a 
                            class="button tiny icon yes black merged left"
                            onclick="
                                '.$this->genCallAjax('ajaxSubscribe', "movim_parse_form('groupsubscribe')", "'".$serverid."'", "'".$groupid."'").'
                                this.onclick=null;"
                        >'.t('Subscribe').'</a><a 
                            class="button tiny icon no black merged right" 
                            onclick="
                                movim_toggle_display(\'#groupsubscribe\');"
                        >'.t('Close').'</a>
                    </div>
                </form>
            </div>
            <div class="popup" id="groupunsubscribe">
                <form name="groupunsubscribe">
                    <fieldset>
                        <legend>'.t('Unsubscribe').'</legend>
                        <div class="element">
                            <label>'.t('Are you sure ?').'</label>
                        </div>
                    </fieldset>
                    <div class="menu">
                        <a 
                            class="button tiny icon yes black merged left"
                            onclick="
                                '.$this->genCallAjax('ajaxUnsubscribe', "'".$serverid."'", "'".$groupid."'").' 
                                this.onclick=null;"
                        >'.t('Unsubscribe').'</a><a 
                            class="button tiny icon no black merged right" 
                            onclick="
                                movim_toggle_display(\'#groupunsubscribe\');"
                        >'.t('Close').'</a>
                    </div>
                </form>
            </div>';
        
        $title = '';
        
        $pd = new modl\PostnDAO();
        $posts = $pd->getNode($serverid, $groupid, 0, 20);
        
        $html .= $title;
        
        if($this->searchSubscription($serverid, $groupid)
        && ($this->role == 'owner' || $this->role == 'publisher'))
            $html .= $this->prepareSubmitForm($serverid, $groupid);

        $html .= $this->preparePosts($posts);
        
        return $html;
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
