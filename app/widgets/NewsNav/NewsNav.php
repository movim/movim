<?php

use Movim\Widget\Base;

include_once WIDGETS_PATH . 'Post/Post.php';

class NewsNav extends Base
{
    public function load()
    {
        $this->addjs('newsnav.js');
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
