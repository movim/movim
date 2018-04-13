<?php

use App\Configuration;
use Movim\Widget\Base;

include_once WIDGETS_PATH . 'Post/Post.php';

class NewsNav extends Base
{
    public function display()
    {
        $blogs = \App\Post::where('open', true)
                          ->restrictToMicroblog()
                          ->orderBy('published', 'desc')
                          ->restrictUserHost()
                          ->take(6)
                          ->get()
                          ->shuffle();

        $this->view->assign('blogs', $blogs);

        $posts = \App\Post::where('open', true)
                          ->orderBy('published', 'desc')
                          ->restrictToCommunities()
                          ->restrictUserHost()
                          ->take(6);

        if ($this->get('s') && $this->get('s') != 'subscriptions') {
            $posts->where('server', $this->get('s'));
        }

        $this->view->assign('posts', $posts->get()->shuffle());
    }

    public function prepareTicket(\App\Post $post)
    {
        return (new \Post)->prepareTicket($post);
    }
}
