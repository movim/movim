<?php

use Movim\Widget\Base;

include_once WIDGETS_PATH . 'Post/Post.php';

class NewsNav extends Base
{
    public function load()
    {
        $this->addjs('newsnav.js');
        $this->registerEvent('pubsub_getitem_handle', 'onItem', 'news');
    }

    public function onItem($packet)
    {
        $post = $packet->content;

        if ($post && $post->isComment()) {
            $post = $post->getParent();
        }

        if ($post) {
            $this->rpc('MovimTpl.fill', '#newsnav #'.cleanupId($post->nodeid), $this->prepareTicket($post));
        }
    }

    public function ajaxHttpGet($page, $server)
    {
        $view = $this->tpl();

        $blogs = collect();

        if ($page == 'news') {
            $blogs = \App\Post::where('open', true)
                ->orderBy('posts.published', 'desc')
                ->restrictToMicroblog()
                ->restrictUserHost()
                ->restrictNSFW()
                ->recents()
                ->take(6)
                ->get()
                ->shuffle();
        }

        $view->assign('blogs', $blogs);

        $posts = \App\Post::where('open', true)
                          ->orderBy('posts.published', 'desc')
                          ->restrictToCommunities()
                          ->restrictUserHost()
                          ->restrictNSFW()
                          ->recents()
                          ->take(6);

        if (isset($server) && $server != 'subscriptions') {
            $posts->where('posts.server', $server);
        }

        $posts = $posts->get()->shuffle();

        if ($posts->isNotEmpty()) {
            $posts = resolveInfos($posts);
        }

        $view->assign('posts', $posts);
        $view->assign('page', $page);

        $this->rpc('MovimTpl.fill', '#newsnav', $view->draw('_newsnav'));
    }

    public function prepareTicket(\App\Post $post)
    {
        return (new \Post)->prepareTicket($post);
    }
}
