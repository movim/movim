<?php

use Movim\Widget\Base;

include_once WIDGETS_PATH . 'Post/Post.php';

class CommunitiesTags extends Base
{
    private function getPosts()
    {
        $posts = \App\Post::withoutComments()
            ->restrictNSFW()
            ->restrictUserHost()
            ->recents()
            ->orderBy('posts.published', 'desc');

        if ($this->_view == 'community') {
            $posts->where('posts.server', $this->get('s'));
        }

        return $posts->where('open', true);
    }

    public function display()
    {
        $posts = $this->getPosts()->take(20)->get('id');

        $tags = \App\Tag::whereIn('id', function ($query) use ($posts) {
            $query->select('tag_id')
                  ->fromSub(function ($query) use ($posts) {
                $query->selectRaw('tag_id, count(tag_id) as count')
                    ->from('post_tag')
                    ->groupBy('tag_id')
                    ->orderBy('count', 'desc')
                    ->whereIn('post_id', $posts->pluck('id'));
            }, 'top');
        })->take(20)->get();

        $this->view->assign('community', ($this->_view == 'community'));
        $this->view->assign('tags', $tags);
    }
}
