<?php

namespace App\Widgets\NewsNav;

use App\Widgets\Post\Post;
use Movim\Widget\Base;

class NewsNav extends Base
{
    public function load()
    {
        $this->addjs('newsnav.js');
    }

    public function ajaxHttpGet($page, $server)
    {
        $view = $this->tpl();

        $posts = \App\Post::where('open', true)
                          ->orderBy('posts.published', 'desc')
                          ->restrictToCommunities()
                          ->restrictUserHost($this->me)
                          ->restrictNSFW($this->me)
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
        return (new Post($this->me))->prepareTicket($post);
    }
}
