<?php

use Movim\Widget\Base;

include_once WIDGETS_PATH . 'Post/Post.php';

class Communities extends Base
{
    private $_page = 30;

    public function load()
    {
        $this->addjs('communities.js');
    }

    public function ajaxHttpGet()
    {
        $this->rpc('MovimTpl.fill', '#communities', $this->prepareCommunities());
    }

    public function ajaxHttpGetPosts($page = 0)
    {
        $this->rpc('MovimTpl.append', '#communities_posts', $this->preparePosts($page));
    }

    public function prepareCommunities()
    {
        $view = $this->tpl();
        return $view->draw('_communities');
    }

    public function preparePosts($page = 0)
    {
        $view = $this->tpl();
        $posts = $this->getPosts()
            ->take($this->_page)
            ->skip($this->_page * $page)
            ->get();
        $view->assign('posts', $posts);
        $view->assign('page',
            $posts->count() == $this->_page
                ? $page + 1
                : 0
        );

        return $view->draw('_communities_posts');
    }

    public function prepareTicket(\App\Post $post)
    {
        return (new \Post)->prepareTicket($post);
    }

    private function getPosts()
    {
        return \App\Post::withoutComments()
            ->restrictNSFW()
            ->restrictUserHost()
            ->recents()
            ->orderBy('posts.published', 'desc')
            ->where('open', true);
    }
}
