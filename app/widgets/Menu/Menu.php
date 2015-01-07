<?php

class Menu extends WidgetCommon
{
    private $_paging = 15;
    
    function load()
    {
        $this->registerEvent('post', 'onPost');
        //$this->registerEvent('stream', 'onStream');

        $this->addjs('menu.js');
    }

    function onStream($count)
    {
        $view = $this->tpl();
        $view->assign('count', $count);
        $view->assign('refresh', $this->call('ajaxGetMenuList', "''", "''", 0));

        RPC::call('movim_posts_unread', $count);
        RPC::call('movim_fill', 'menu_refresh', $view->draw('_menu_refresh', true));
    }

    function onPost($packet)
    {
        $pd = new \Modl\PostnDAO;
        $count = $pd->getCountSince(Cache::c('since'));

        if($count > 0) {
            $post = $packet->content;
            if($post->isMicroblog()) {
                $cd = new \Modl\ContactDAO;
                $contact = $cd->get($post->jid);

                if($post->title == null) {
                    $title = __('post.default_title');
                } else {
                    $title = $post->title;
                }

                Notification::append('news', $contact->getTrueName(), $title, $contact->getPhoto('s'), 2);
            } else {
                Notification::append('news', $post->title, $post->node, null, 2);
            }

            $this->onStream($count);
        }

    }

    function ajaxGetAll($page = 0)
    {
        $this->prepareList('all', null, null, $page);
    }

    function ajaxGetNews($page = 0)
    {
        $this->prepareList('news', null, null, $page);
    }

    function ajaxGetFeed($page = 0)
    {
        $this->prepareList('feed', null, null, $page);
    }

    function ajaxGetNode($server = null, $node = null, $page = 0)
    {
        $this->prepareList('node', $server, $node, $page);
    }

    function prepareList($type = 'all', $server = null, $node = null, $page = 0) {

        $view = $this->tpl();
        $pd = new \Modl\PostnDAO;

        Cache::c('since', date(DATE_ISO8601, strtotime($pd->getLastDate())));

        $next = $page + 1;

        switch($type) {
            case 'all' :
                $view->assign('history', $this->call('ajaxGetAll', $next));
                $items  = $pd->getAllPosts(false, $page*$this->_paging, $this->_paging);
                break;
            case 'news' :
                $view->assign('history', $this->call('ajaxGetNews', $next));
                $items  = $pd->getNews($page*$this->_paging, $this->_paging);
                break;
            case 'feed' :
                $view->assign('history', $this->call('ajaxGetFeed', $next));
                $items  = $pd->getFeed($page*$this->_paging, $this->_paging);
                break;
            case 'node' :
                $view->assign('history', $this->call('ajaxGetNode', '"'.$server.'"', '"'.$node.'"', $next));
                $items  = $pd->getNode($server, $node, $page*$this->_paging, $this->_paging);
                break;
        }
        /*
        if($server == null || $node == null) {
            $view->assign('history', $this->call('ajaxGetMenuList', "''", "''", $next));
            $items  = $pd->getNews($page*$this->_paging, $this->_paging);
        } else {
            $view->assign('history', $this->call('ajaxGetMenuList', '"'.$server.'"', '"'.$node.'"', $next));
            $items  = $pd->getNode($server, $node, $page*$this->_paging, $this->_paging);
        }*/
        
        $view->assign('items', $items);
        $view->assign('page', $page);

        $html = $view->draw('_menu_list', true);

        if($page > 0) {
            RPC::call('movim_append', 'menu_widget', $html);
        } else {
            RPC::call('movim_fill', 'menu_widget', $html);
            RPC::call('movim_posts_unread', 0);
        }
        RPC::call('Menu.refresh');
    }

    function ajaxGetMenuList($server = null, $node = null, $page = 0)
    {
        $html = $this->prepareMenuList($server, $node, $page);

        if($page > 0) {
            RPC::call('movim_append', 'menu_widget', $html);
        } else {
            RPC::call('movim_fill', 'menu_widget', $html);
            RPC::call('movim_posts_unread', 0);
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
