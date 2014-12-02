<?php

/**
 * @package Widgets
 *
 * @file Menu.php
 * This file is part of Movim.
 *
 * @brief General Menu
 *
 * @author Jaussoin Timothée <edhelas_at_movim_dot_com>
 *
 * @version 1.0
 * @date 1 december 2014
 *
 * Copyright (C)2014 MOVIM project
 *
 * See COPYING for licensing information.
 */

class Menu extends WidgetCommon
{
    private $_paging = 15;
    
    function load()
    {
        $this->registerEvent('post', 'onStream');
        $this->registerEvent('stream', 'onStream');
        
        $this->addcss('menu.css');
        $this->addjs('menu.js');
    }

    function onStream()
    {
        $pd = new \Modl\PostnDAO;
        $count = $pd->getCountSince(Cache::c('since'));

        if($count > 0) {
            $view = $this->tpl();
            $view->assign('count', $count);
            $view->assign('refresh', $this->call('ajaxGetMenuList', "''", "''", 0));

            RPC::call('movim_posts_unread', $count);
            RPC::call('movim_fill', 'menu_refresh', $view->draw('_menu_refresh', true));
        }
    }

    function ajaxGetMenuList($server = null, $node = null, $page = 0)
    {
        $html = $this->prepareMenuList($server, $node, $page);

        if($page > 0) {
            RPC::call('movim_append', 'menu_widget', $html);
        } else {
            RPC::call('movim_fill', 'menu_widget', $html);
        }
        RPC::call('Menu.refresh');
    }

    function prepareMenuList($server = null, $node = null, $page = 0)
    {
        $view = $this->tpl();
        $pd = new \Modl\PostnDAO;

        Cache::c('since', date(DATE_ISO8601, strtotime($pd->getLastDate())));

        $next = $page + 1;

        if($server == null || $node == null) {
            $view->assign('history', $this->call('ajaxGetMenuList', "''", "''", $next));
            $items  = $pd->getNews($page*$this->_paging, $this->_paging);
        } else {
            $view->assign('history', $this->call('ajaxGetMenuList', '"'.$server.'"', '"'.$node.'"', $next));
            $items  = $pd->getNode($server, $node, $page*$this->_paging, $this->_paging);
        }
        
        $view->assign('items', $items);
        $view->assign('page', $page);

        return $view->draw('_menu_list', true);
    }

    function display()
    {
    }
}
