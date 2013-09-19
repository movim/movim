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

class Bookmark extends WidgetBase
{
    function WidgetLoad()
    {
        $this->addcss('bookmark.css');
        $this->registerEvent('bookmark', 'onBookmark');
        $this->registerEvent('bookmarkerror', 'onBookmarkError');

        $this->registerEvent('groupsubscribed', 'onGroupSubscribed');
        $this->registerEvent('groupunsubscribed', 'onGroupUnsubscribed');

        $this->view->assign('getbookmark',      $this->genCallAjax("ajaxGetBookmark"));
        $this->view->assign('setbookmark',      $this->genCallAjax("ajaxSetBookmark", "''"));
        $this->view->assign('preparebookmark',  $this->prepareBookmark(Cache::c('bookmark')));
    }
    
    function onGroupSubscribed()
    {
        $arr = Cache::c('bookmark');

        $html = $this->prepareBookmark($arr);     
        RPC::call('movim_fill', 'bookmarks', $html);   
        RPC::call('setBookmark');   
    }
    
    function onGroupUnsubscribed()
    {
        $arr = Cache::c('bookmark');
        
        $html = $this->prepareBookmark($arr);  
        RPC::call('movim_fill', 'bookmarks', $html);   
        RPC::call('setBookmark');        
    }
    
    function onBookmark($arr)
    {
        $i = 0;
        foreach($arr as $b) {
            if($b['type'] == 'subscription') {
                $su = new \modl\Subscription();
                $su->jid    = $this->user->getLogin();
                $su->server = $b['server'];
                $su->node   = $b['node'];
                $su->subscription   = 'subscribed';
                $su->subid  = $b['subid'];
                $su->timestamp      = date('Y-m-d H:i:s', rand(1111111111, 8888888888));
            
                $sd = new \modl\SubscriptionDAO();
                $sd->set($su);
                
                unset($arr[$i]);
            }
            $i++;
        }
        
        Cache::c('bookmark', $arr);
        $html = $this->prepareBookmark($arr);
        RPC::call('movim_fill', 'bookmarks', $html);
        Notification::appendNotification(t('Bookmarks updated'), 'info');
    }
    
    function onBookmarkError($error)
    {
        Notification::appendNotification(t('An error occured : ').$error, 'error');
    }
    
    function ajaxGetBookmark() 
    {
        $b = new moxl\BookmarkGet();
        $b->request();
    }
    
    function ajaxSetBookmark($arr = null) 
    {            
        if($arr == null || $arr == '')
            $arr = Cache::c('bookmark');
        if($arr == null)
            $arr = array();
        
        $sd = new \modl\SubscriptionDAO();

        if($sd != null) {
            foreach($sd->getSubscribed() as $s) {
                array_push($arr,
                    array(
                        'type'      => 'subscription',
                        'server'    => $s->server,
                        'title'     => $s->title,
                        'subid'     => $s->subid,
                        'node'      => $s->node));   
            }
        }
        
        $b = new moxl\BookmarkSet();
        $b->setArr($arr)
          ->request();
    }
    
    // Add a new MUC
    function ajaxBookmarkMucAdd($form) 
    {
        if(!filter_var($form['jid'], FILTER_VALIDATE_EMAIL)) {
            $html = '<div class="message error">'.t('Bad Chatroom ID').'</div>' ;
            RPC::call('movim_fill', 'bookmarkmucadderror', $html);
            RPC::commit();
        } elseif(trim($form['name']) == '') {
            $html = '<div class="message error">'.t('Empty name').'</div>' ;
            RPC::call('movim_fill', 'bookmarkmucadderror', $html);
            RPC::commit();            
        } else {
        
            $bookmarks = Cache::c('bookmark');        
                    
            if($bookmarks == null)
                $bookmarks = array();
            
            array_push($bookmarks,
                array(
                    'type'      => 'conference',
                    'name'      => $form['name'],
                    'autojoin'  => $form['autojoin'],
                    'nick'      => $form['nick'],
                    'jid'       => $form['jid']));   
            
            $this->ajaxSetBookmark($bookmarks);
        }
    }
    
    // Remove a MUC
    function ajaxBookmarkMucRemove($jid)
    {
        $arr = Cache::c('bookmark');
        foreach($arr as $key => $b) {
            if($b['type'] == 'conference' && $b['jid'] == $jid)
                unset($arr[$key]);
        }

        $b = new moxl\BookmarkSet();
        $b->setArr($arr)
          ->request();
    }
    
    // Join a MUC 
    function ajaxBookmarkMucJoin($jid, $nickname)
    {
        $p = new moxl\PresenceMuc();
        $p->setTo($jid)
          ->setNickname($nickname)
          ->request();
    }
    
    // Add a new URL
    function ajaxBookmarkUrlAdd($form) 
    {
        if(!filter_var($form['url'], FILTER_VALIDATE_URL)) {
            $html = '<div class="message error">'.t('Bad URL').'</div>' ;
            RPC::call('movim_fill', 'bookmarkadderror', $html);
            RPC::commit();
        } elseif(trim($form['name']) == '') {
            $html = '<div class="message error">'.t('Empty name').'</div>' ;
            RPC::call('movim_fill', 'bookmarkadderror', $html);
            RPC::commit();            
        } else {
        
            $bookmarks = Cache::c('bookmark');        
                    
            if($bookmarks == null)
                $bookmarks = array();
            
            array_push($bookmarks,
                array(
                    'type'      => 'url',
                    'name'      => $form['name'],
                    'url'       => $form['url']));   
            
            $this->ajaxSetBookmark($bookmarks);
        }
    }
    
    // Remove an URL
    function ajaxBookmarkUrlRemove($url)
    {
        $arr = Cache::c('bookmark');
        foreach($arr as $key => $b) {
            if($b['type'] == 'url' && $b['url'] == $url)
                unset($arr[$key]);
        }

        $b = new moxl\BookmarkSet();
        $b->setArr($arr)
          ->request();
    }
    
    function prepareBookmark($bookmarks)
    {
        $html = '';
        $url = '';
        $conference = '';
        $subscription = '';
        
        $urlnum = $conferencenum = $subscriptionnum = 0;

        $sd = new \modl\SubscriptionDAO();
        
        if($sd != null && $sd->getSubscribed() != null) {
        
            foreach($sd->getSubscribed() as $s) {
                if($s->name != null)
                    $name = $s->name;
                else
                    $name = $s->node;
                
                $subscription .= '
                    <li>
                        <a href="'.Route::urlize('node', array($s->server, $s->node)).'">'.
                            $name.' 
                        </a>
                    </li>';
                    
                $subscriptionnum++;
            }
        }
        
        if($bookmarks == null)
            $bookmarks = array();
        
        foreach($bookmarks as $b) {
            switch ($b['type']) {
            case 'conference':
                $remove = $this->genCallAjax('ajaxBookmarkMucRemove', "'".$b['jid']."'");
                $join   = $this->genCallAjax(
                            'ajaxBookmarkMucJoin',   
                            "'".$b['jid']."'",
                            "'".$b['nick']."'");
                $conference .= '
                    <li>
                        <a href="#" onclick="'.$join.'">'.$b['name'].'</a>
                        <a href="#" onclick="'.$remove.'">X</a>
                    </li>';
                
                $conferencenum++;
                break;
            case 'url':
                $remove = $this->genCallAjax('ajaxBookmarkUrlRemove', "'".$b['url']."'");
                $url .= '
                    <li>
                        <a target="_blank" href="'.$b['url'].'">'.
                            $b['name'].'
                        </a>
                        <a href="#" onclick="'.$remove.'">X</a>
                    </li>';
                    
                $urlnum++;
                break;
            }
        }
            
        if($conference != '') {
            $html .= '
                <h3>'.t('Conferences').' - '.$conferencenum.'</h3>
                <ul>'.
                    $conference.'
                </ul>';
        }
        
        if($subscription != '') {
            $html .= '
                <h3>'.t('Groups').' - '.$subscriptionnum.'</h3>
                <ul>'.
                    $subscription.'
                </ul>';
        }
        
        if($url != '') {
            $html .= '                
                <h3>'.t('Links').' - '.$urlnum.'</h3>
                <ul>'.
                    $url.'
                </ul>';
        }
          
        // The URL add form
        $urlview = $this->tpl();
        $urlview->assign(
            'submit', 
            $this->genCallAjax(
                'ajaxBookmarkUrlAdd', 
                "movim_parse_form('bookmarkurladd')")
        );
        $html .= $urlview->draw('_bookmark_url_add', true);
        
        // The MUC add form
        $mucview = $this->tpl();
        $mucview->assign(
            'submit', 
            $this->genCallAjax(
                'ajaxBookmarkMucAdd', 
                "movim_parse_form('bookmarkmucadd')")
        );
        $html .= $mucview->draw('_bookmark_muc_add', true);
        
        return $html;
    }
}
