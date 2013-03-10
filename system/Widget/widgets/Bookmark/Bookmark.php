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
        $this->registerEvent('bookmark', 'onBookmark');
    }
    
    function onBookmark($arr)
    {
        Cache::c('bookmark', $arr);
        $html = $this->prepareBookmark($arr);
        RPC::call('movim_fill', 'bookmarks', RPC::cdata($html));
    }
    
    function ajaxGetBookmark() 
    {
        $b = new moxl\BookmarkGet();
        $b->request();
    }
    
    function ajaxSetBookmark() 
    {
        $b = new moxl\BookmarkSet();                
        $bookmarks = Cache::c('bookmark');
                
        if($bookmarks == null)
            $bookmarks = array();
        
        $b->setArr($arr)
          ->request();
    }
    
    function ajaxBookmarkAdd($form) 
    {
        if(!filter_var($form['url'], FILTER_VALIDATE_URL)) {
            $html = '<div class="message error">'.t('Bad URL').'</div>' ;
            RPC::call('movim_fill', 'bookmarkadderror', RPC::cdata($html));
            RPC::commit();
        } elseif(trim($form['name']) == '') {
            $html = '<div class="message error">'.t('Empty name').'</div>' ;
            RPC::call('movim_fill', 'bookmarkadderror', RPC::cdata($html));
            RPC::commit();            
        }
        
        $bookmarks = Cache::c('bookmark');        
                
        if($bookmarks == null)
            $bookmarks = array();
        
        array_push($bookmarks,
            array(
                'type'      => 'url',
                'name'      => $form['name'],
                'url'       => $form['url']));   
       
        $b = new moxl\BookmarkSet();
        $b->setArr($bookmarks)
          ->request();
    }
    
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
        
        if($bookmarks == null)
            $bookmarks = array();
        
        foreach($bookmarks as $b) {
            switch ($b['type']) {
            case 'conference':
                $conference .= '
                    <li>'.$b['name'].'</li>';
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
                break;
            case 'subscription':
                $subscription .= '
                    <li>
                        <a href="?q=node&s='.$b['server'].'&n='.$b['node'].'">'.
                            $b['title'].'
                        </a>
                    </li>';
                break;
            }
        }
        
        $html .= '
            <h3>'.t('Groups').'</h3>
            <ul>'.
                $subscription.'
            </ul>
            
            <h3>'.t('Links').'</h3>
            <ul>'.
                $url.'
            </ul>
            
            <h3>'.t('Conferences').'</h3>
            <ul>'.
                $conference.'
            </ul>';
            
        $submit = $this->genCallAjax('ajaxBookmarkAdd', "movim_parse_form('bookmarkadd')");
            
        $html .= '
            <div class="popup" id="bookmarkadd">
                <form name="bookmarkadd">
                    <fieldset>
                        <legend>'.t('Add a new URL').'</legend>
                        
                        <div id="bookmarkadderror"></div>
                        <div class="element large mini">
                            <input name="url" placeholder="'.t('URL').'"/>
                        </div>
                        <div class="element large mini">
                            <input name="name" placeholder="'.t('Name').'"/>
                        </div>
                    </fieldset>
                    <a 
                        class="button tiny icon yes black merged left"
                        onclick="'.$submit.'"
                    >'.
                            t('Add').'
                    </a><a 
                        class="button tiny icon black merged right" 
                        onclick="this.parentNode.parentNode.style.display = \'none\'"
                    >'.
                            t('Close').'
                    </a>
                </form>
            </div>
        ';
        
        return $html;
    }
    
    function build()
    {
        $getbookmark = $this->genCallAjax("ajaxGetBookmark");
        $setbookmark = $this->genCallAjax("ajaxSetBookmark");
    ?>
        <h2><?php echo t('Bookmarks'); ?></h2>
    
        <div id="bookmarks">
            <?php echo $this->prepareBookmark(Cache::c('bookmark')); ?>
        </div>

        <a class="button icon yes tiny merged right" style="float: right;"
           onclick="movim_toggle_display('#bookmarkadd')">Add</a>
        <a class="button icon yes tiny merged left" style="float: right;"
           onclick="<?php echo $getbookmark; ?>">Refresh</a>
        <br />
        <?php 
    }
}
