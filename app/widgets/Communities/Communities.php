<?php

use Movim\Widget\Base;

use Respect\Validation\Validator;
use App\Configuration;
use Carbon\Carbon;

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
            ->where('open', true);

        $postsIds = $posts->take(200)->pluck('id')->toArray();

        $tags = \App\Tag::whereIn('id', function ($query) use ($postsIds) {
            $query->select('tag_id')
                  ->fromSub(function ($query) use ($postsIds) {
                      $query->selectRaw('tag_id, count(tag_id) as count')
                            ->fromSub(function ($query) use ($postsIds) {
                                $query->from('post_tag')
                                      ->whereIn('post_id', $postsIds)
                                      ->get();
                            }, 'last_month')
                            ->groupBy('tag_id')
                            ->orderBy('count', 'desc')
                            ->take(20);
                  }, 'top');
        })->get();

        $view->assign('tags', $tags);
        $view->assign('posts', $posts->take(30)->get());
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
