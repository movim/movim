<?php

use App\Configuration;
use Movim\Widget\Base;

include_once WIDGETS_PATH . 'Post/Post.php';

class NewsNav extends Base
{
    public function display()
    {
        $blogs = \App\Post::where('open', true)
                          ->orderBy('posts.published', 'desc')
                          ->restrictToMicroblog()
                          ->restrictUserHost()
                          ->restrictNSFW()
                          ->recents()
                          ->take(6)
                          ->get()
                          ->shuffle();

        $this->view->assign('blogs', $blogs);

        $posts = \App\Post::where('open', true)
                          ->orderBy('posts.published', 'desc')
                          ->restrictToCommunities()
                          ->restrictUserHost()
                          ->restrictNSFW()
                          ->recents()
                          ->take(6);

        if ($this->get('s') && $this->get('s') != 'subscriptions') {
            $posts->where('posts.server', $this->get('s'));
        }

        $this->view->assign('posts', $posts->get()->shuffle());
    }

    public function prepareTicket(\App\Post $post)
    {
        return (new \Post)->prepareTicket($post);
    }
}
