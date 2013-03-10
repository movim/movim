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
		/*$this->addcss('config.css');
		$this->addjs('color/jscolor.js');*/
        $this->registerEvent('bookmark', 'onBookmark');
    }
    
    function onBookmark($arr)
    {
        Cache::c('bookmark', $arr);
    }
    
    function ajaxGetBookmark() 
    {
        $b = new moxl\BookmarkGet();
        $b->request();
    }
    
    function ajaxSetBookmark() 
    {
        $b = new moxl\BookmarkSet();
        /*$arr = array();
        array_push($arr,
            array(
                'type'      => 'conference',
                'name'      => 'Movim',
                'autojoin'  => '1',
                'jid'  => 'movim@conference.movim.eu',
                'nick'  => 'edhelas'));
        array_push($arr,
            array(
                'type'      => 'url',
                'name'      => 'HFR',
                'url'       => 'http://forum.hardware.fr'));
        array_push($arr,
            array(
                'type'      => 'url',
                'name'      => 'LeMonde',
                'url'       => 'http://www.lemonde.fr'));
        array_push($arr,
            array(
                'type'      => 'subscription',
                'server'    => 'pubsub.etu.univ-nantes.fr',
                'node'      => 'mlp',
                'title'     => 'My Little Pony'));*/
                
        $arr = Cache::c('bookmark');
        
        $b->setArr($arr)
          ->request();
    }
    
    function ajaxBookmarkAdd($form) 
    {
        movim_log($form);
        if(!filter_var($form['url'], FILTER_VALIDATE_URL)) {
            $html = '<div class="message error">'.t('Bad URL').'</div>' ;
            RPC::call('movim_fill', 'bookmarkadderror', RPC::cdata($html));
            RPC::commit();
        }
            
    }
    
    function prepareBookmark($bookmarks)
    {
        $html = '';
        $url = '';
        $conference = '';
        $subscription = '';
        
        foreach($bookmarks as $b) {
            switch ($b['type']) {
            case 'conference':
                $conference .= '
                    <li>'.$b['name'].'</li>';
                break;
            case 'url':
                $url .= '
                    <li>
                        <a target="_blank" href="'.$b['url'].'">'.
                            $b['name'].'
                        </a>
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
            <div class="popup">
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
        <a class="button icon yes tiny"
           onclick="<?php echo $getbookmark; ?>">Get</a>
        <a class="button icon yes tiny"
           onclick="<?php echo $setbookmark; ?>">Set</a>
    <?php
        echo $this->prepareBookmark(Cache::c('bookmark'));
    }
}
