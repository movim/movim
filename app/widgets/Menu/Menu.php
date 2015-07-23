<?php

use Moxl\Xec\Action\Pubsub\GetItems;

class Menu extends WidgetBase
{
    private $_paging = 15;

    function load()
    {
        $this->registerEvent('post', 'onPost');
        $this->registerEvent('post_retract', 'onRetract');
        $this->addjs('menu.js');
        $this->addcss('menu.css');
    }

    function onRetract($packet)
    {
        $this->ajaxGetAll();
    }

    function onStream($count)
    {
        $view = $this->tpl();
        $view->assign('count', $count);
        $view->assign('refresh', $this->call('ajaxGetAll'));

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
                $contact = $cd->get($post->origin);

                if($post->title == null) {
                    $title = __('post.default_title');
                } else {
                    $title = $post->title;
                }

                if(!$post->isMine()) Notification::append('news', $contact->getTrueName(), $title, $contact->getPhoto('s'), 2);
            } else {
                Notification::append('news', $post->title, $post->node, null, 2);
            }

            $this->onStream($count);
        }
    }

    function ajaxGetAll($page = 0)
    {
        $this->ajaxGet('all', null, null, $page);
    }

    function ajaxGetNews($page = 0)
    {
        $this->ajaxGet('news', null, null, $page);
    }

    function ajaxGetFeed($page = 0)
    {
        $this->ajaxGet('feed', null, null, $page);
    }

    function ajaxGetNode($server = null, $node = null, $page = 0)
    {
        $this->ajaxGet('node', $server, $node, $page);
    }

    function ajaxGet($type = 'all', $server = null, $node = null, $page = 0)
    {
        $html = $this->prepareList($type, $server, $node, $page);

        if($page > 0) {
            RPC::call('movim_append', 'menu_wrapper', $html);
        } else {
            RPC::call('movim_fill', 'menu_widget', $html);
            RPC::call('movim_posts_unread', 0);
        }
        RPC::call('Menu.refresh');
    }

    function ajaxRefresh()
    {
        Notification::append(null, $this->__('menu.refresh'));

        $sd = new \modl\SubscriptionDAO();
        $subscriptions = $sd->getSubscribed();

        foreach($subscriptions as $s) {
            $r = new GetItems;
            $r->setTo($s->server)
              ->setNode($s->node)
              ->request();
        }
    }

    function prepareList($type = 'all', $server = null, $node = null, $page = 0) {
        $view = $this->tpl();
        $pd = new \Modl\PostnDAO;
        $count = $pd->getCountSince(Cache::c('since'));

        // getting newer, not older
        if($page == 0 || $page == ""){
            $count = 0;
            Cache::c('since', date(DATE_ISO8601, strtotime($pd->getLastDate())));
        }

        $next = $page + 1;

        switch($type) {
            case 'all' :
                $view->assign('history', $this->call('ajaxGetAll', $next));
                $items  = $pd->getAllPosts(false, $page * $this->_paging + $count, $this->_paging);
                break;
            case 'news' :
                $view->assign('history', $this->call('ajaxGetNews', $next));
                $items  = $pd->getNews($page * $this->_paging + $count, $this->_paging);
                break;
            case 'feed' :
                $view->assign('history', $this->call('ajaxGetFeed', $next));
                $items  = $pd->getFeed($page * $this->_paging + $count, $this->_paging);
                break;
            case 'node' :
                $view->assign('history', $this->call('ajaxGetNode', '"'.$server.'"', '"'.$node.'"', '"'.$next.'"'));
                $items  = $pd->getNode($server, $node, $page * $this->_paging + $count, $this->_paging);
                break;
        }

        $view->assign('items', $items);
        $view->assign('page', $page);
        $view->assign('paging', $this->_paging);

        $html = $view->draw('_menu_list', true);

        if($page == 0 || $page == ""){
            $view = $this->tpl();
            $view->assign('to', $this->user->getLogin());
            $html .= $view->draw('_menu_add', true);
        }

        return $html;
    }

    function display()
    {
    }
}
