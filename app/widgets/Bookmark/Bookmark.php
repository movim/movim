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
    private $_list_server;
    
    function load()
    {
        $this->addcss('bookmark.css');
        $this->registerEvent('bookmark', 'onBookmark');
        $this->registerEvent('bookmarkerror', 'onBookmarkError');
        
        //$this->registerEvent('mucrole', 'onMucRole');

        $this->registerEvent('groupsubscribed', 'onGroupSubscribed');
        $this->registerEvent('groupunsubscribed', 'onGroupUnsubscribed');
    }

    function display()
    {        
        $this->view->assign('subscriptionconfig', Route::urlize('conf', false, 'groupsubscribedlistconfig'));

        $this->view->assign('getbookmark',      $this->genCallAjax("ajaxGetBookmark"));
        $this->view->assign('setbookmark',      $this->genCallAjax("ajaxSetBookmark", "''"));
        
        $this->view->assign('preparebookmark',  $this->prepareBookmark());
    }

    function prepareBookmark() {
        $cd = new \modl\ConferenceDAO();
        $sd = new \modl\SubscriptionDAO();
        
        // The URL add form
        $listview = $this->tpl();
        $listview->assign('conferences', $cd->getAll());
        $listview->assign('subscriptions', $sd->getSubscribed());

        $html = '';

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

        $html .= $listview->draw('_bookmark_list', true);
        return $html;
    }

    function checkNewServer($node) {
        $r = false;
        
        if($this->_list_server != $node->server)
            $r = true;

        $this->_list_server = $node->server;
        return $r;
    }

    function getMucRemove($node) {
        return $this->genCallAjax(
            'ajaxBookmarkMucRemove',
            "'".$node->conference."'"
            );
    }
    
    function getMucJoin($node) {
        return $this->genCallAjax(
            'ajaxBookmarkMucJoin',
            "'".$node->conference."'",
            "'".$node->nick."'"
            );
    }
    
    function onGroupSubscribed()
    {
        $html = $this->prepareBookmark();     
        RPC::call('movim_fill', 'bookmarks', $html);   
        RPC::call('setBookmark');   
    }
    
    function onGroupUnsubscribed()
    {
        $html = $this->prepareBookmark();  
        RPC::call('movim_fill', 'bookmarks', $html);   
        RPC::call('setBookmark');        
    }
    
    function onBookmark()
    {
        $html = $this->prepareBookmark();
        RPC::call('movim_fill', 'bookmarks', $html);
        Notification::appendNotification(t('Bookmarks updated'), 'info');
    }
    /*
    function onMucRole($arr)
    {

    }
    */
    function onBookmarkError($error)
    {
        Notification::appendNotification(t('An error occured : ').$error, 'error');
    }
    
    function ajaxGetBookmark() 
    {
        $b = new moxl\BookmarkGet();
        $b->setTo($this->user->getLogin())
          ->request();
    }
    
    function ajaxSetBookmark($item = false) 
    {
        $arr = array();

        if($item) {
            array_push($arr, $item);
        }
        
        $sd = new \modl\SubscriptionDAO();
        $cd = new \modl\ConferenceDAO();

        foreach($sd->getSubscribed() as $s) {
            array_push($arr,
                array(
                    'type'      => 'subscription',
                    'server'    => $s->server,
                    'title'     => $s->title,
                    'subid'     => $s->subid,
                    'tags'      => unserialize($s->tags),
                    'node'      => $s->node));   
        }

        foreach($cd->getAll() as $c) {
            array_push($arr,
                array(
                    'type'      => 'conference',
                    'name'      => $c->name,
                    'autojoin'  => $c->autojoin,
                    'nick'      => $c->nick,
                    'jid'       => $c->conference)); 
        }

        
        $b = new moxl\BookmarkSet();
        $b->setArr($arr)
          ->setTo($this->user->getLogin())
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
            $item = array(
                    'type'      => 'conference',
                    'name'      => $form['name'],
                    'autojoin'  => $form['autojoin'],
                    'nick'      => $form['nick'],
                    'jid'       => $form['jid']);   
            
            $this->ajaxSetBookmark($item);
        }
    }
    
    // Remove a MUC
    function ajaxBookmarkMucRemove($jid)
    {
        $cd = new \modl\ConferenceDAO();
        $cd->deleteNode($jid);
        
        $this->ajaxSetBookmark();
    }
    
    // Join a MUC 
    function ajaxBookmarkMucJoin($jid, $nickname)
    {
        $p = new moxl\PresenceMuc();
        $p->setTo($jid)
          ->setNickname($nickname)
          ->request();
    }
    /*
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
            $server = null;
        
            foreach($sd->getSubscribed() as $s) {
                if($s->name != null)
                    $name = $s->name;
                else
                    $name = $s->node;
                    
                if($server != $s->server) {
                    $subscription .= '
                            <a href="'.Route::urlize('server', array($s->server)).'"><h3>'.
                                $s->server.' 
                            </h3></a>'; 
                    $server = $s->server;
                }
                
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
                <h2>'.t('Conferences').' - '.$conferencenum.'</h2>
                <ul>'.
                    $conference.'
                </ul>';
        }
        
        if($subscription != '') {
            $html .= '
                <h2>'.t('Groups').' - '.$subscriptionnum.'</h2>
                <ul>'.
                    $subscription.'
                </ul>';
        }
        
        if($url != '') {
            $html .= '                
                <h2>'.t('Links').' - '.$urlnum.'</h2>
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
    */
}
