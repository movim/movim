<?php

use Movim\Widget\Base;

include_once WIDGETS_PATH . 'Post/Post.php';

class Communities extends Base
{
    public function load()
    {
        $this->addjs('communities.js');
    }

    public function ajaxHttpGet()
    {
        $this->rpc('MovimTpl.fill', '#communities', $this->prepareCommunities());
    }

    public function prepareCommunities()
    {
        $view = $this->tpl();

        $posts = \App\Post::withoutComments()
            ->restrictNSFW()
            ->restrictUserHost()
            ->recents()
            ->orderBy('posts.published', 'desc')
            ->where('open', true);

        $posts = $posts->take(30)->get();

        $tags = \App\Tag::whereIn('id', function ($query) use ($posts) {
            $query->select('tag_id')
                  ->fromSub(function ($query) use ($posts) {
                $query->selectRaw('tag_id, count(tag_id) as count')
                    ->from('post_tag')
                    ->groupBy('tag_id')
                    ->orderBy('count', 'desc')
                    ->whereIn('post_id', $posts->pluck('id'));
            }, 'top');
        })->get();

        $view->assign('tags', $tags);
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
