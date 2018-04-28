<?php

use Respect\Validation\Validator;
use App\Configuration;

include_once WIDGETS_PATH . 'Post/Post.php';

class Communities extends \Movim\Widget\Base
{
    public function load()
    {
        $this->addjs('communities.js');
        $this->addcss('communities.css');
    }

    function ajaxGet()
    {
        $this->rpc('MovimTpl.fill', '#communities', $this->prepareCommunities());
    }

    function prepareCommunities()
    {
        $view = $this->tpl();

        $posts = \App\Post::withoutComments()
            ->restrictToCommunities()
            ->restrictNSFW()
            ->recents()
            ->orderBy('posts.published', 'desc')
            ->where('open', true)
            ->take(50)
            ->get();

        $view->assign('posts', $posts);

        return $view->draw('_communities', true);
    }

    public function prepareTicket(\App\Post $post)
    {
        return (new \Post)->prepareTicket($post, true);
    }
}
