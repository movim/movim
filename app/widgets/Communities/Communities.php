<?php

use Movim\Widget\Base;

include_once WIDGETS_PATH . 'Post/Post.php';

class Communities extends Base
{
    private $_page = 18;

    public function load()
    {
        $this->addjs('communities.js');
    }

    public function ajaxHttpGetAll()
    {
        $this->rpc('MovimTpl.fill', '#communities', $this->prepareCommunities());
        $this->rpc('MovimTpl.fill', '#communities_posts', $this->preparePosts());
    }

    public function ajaxHttpGetNews()
    {
        $this->rpc('MovimTpl.fill', '#communities', $this->prepareCommunities('news'));
        $this->rpc('MovimTpl.fill', '#communities_posts', $this->preparePosts(0, 'news'));
    }

    public function ajaxHttpGetFeed()
    {
        $this->rpc('MovimTpl.fill', '#communities', $this->prepareCommunities('feed'));
        $this->rpc('MovimTpl.fill', '#communities_posts', $this->preparePosts(0, 'feed'));
    }

    public function ajaxHttpGetPosts($page = 0, $type = 'all')
    {
        $this->rpc('MovimTpl.append', '#communities_posts', $this->preparePosts($page, $type));
    }

    public function prepareCommunities($type = 'all')
    {
        $view = $this->tpl();
        $view->assign('type', $type);

        return $view->draw('_communities');
    }

    public function preparePosts($page = 0, $type = 'all')
    {
        $view = $this->tpl();
        $posts = $this->getPosts();

        if ($type == 'news') {
            $posts = $posts->restrictToCommunities();
        } elseif ($type == 'feed') {
            $posts = $posts->restrictToMicroblog();
        }

        $posts = $posts->take(
                ($page == 0 && $type == 'all')
                    ? $this->_page -1
                    : $this->_page
            )
            ->skip(
                ($page != 0 && $type == 'all')
                    ? (($this->_page * $page) - 1)
                    : ($this->_page * $page)
            )
            ->get();

        if ($posts->isNotEmpty()) {
            $posts = resolveInfos($posts);
        }

        $view->assign('posts', $posts);
        $view->assign('type', $type);
        $view->assign('limit', $this->_page);
        $count = ($page == 0 && $type == 'all') ? $this->_page - 1 : $this->_page;
        $view->assign('page',
            $posts->count() == $count
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
