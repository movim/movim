<?php

use Movim\Widget\Base;

include_once WIDGETS_PATH . 'Post/Post.php';

class CommunitiesTags extends Base
{
    public function load()
    {
        $this->addjs('communitiestags.js');
    }

    public function ajaxHttpGet()
    {
        $this->rpc('MovimTpl.fill', '#communitiestags', $this->prepareCommunitiesTags());
    }

    public function prepareCommunitiesTags()
    {
        $view = $this->tpl();

        $posts = $this->getPosts()->take(60)->get('id');

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

        return $view->draw('_communitiestags');
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
