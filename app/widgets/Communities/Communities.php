<?php

use Movim\Widget\Base;

use Respect\Validation\Validator;
use App\Configuration;

include_once WIDGETS_PATH . 'Post/Post.php';

class Communities extends Base
{
    public function load()
    {
        $this->addjs('communities.js');
    }

    public function ajaxGet()
    {
        $this->rpc('MovimTpl.fill', '#communities', $this->prepareCommunities());
    }

    public function prepareCommunities()
    {
        $view = $this->tpl();

        $posts = \App\Post::withoutComments()
            ->restrictToCommunities()
            ->restrictNSFW()
            ->recents()
            ->orderBy('posts.published', 'desc')
            ->where('open', true)
            ->take(30)
            ->get();

        $view->assign('posts', $posts);
        $view->assign('communities', $this->user->session->interestingCommunities()
            ->take(6)
            ->get());

        return $view->draw('_communities');
    }

    public function prepareTicket(\App\Post $post)
    {
        return (new \Post)->prepareTicket($post);
    }
}
