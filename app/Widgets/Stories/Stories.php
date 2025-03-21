<?php

namespace App\Widgets\Stories;

use App\Post;
use App\Widgets\Toast\Toast;
use Movim\Widget\Base;

class Stories extends Base
{
    public function load()
    {
        $this->registerEvent('post', 'onStory');
        $this->registerEvent('pubsub_getitem_handle', 'onStory');
        $this->registerEvent('pubsub_postdelete_handle', 'onDelete');

        $this->addjs('stories.js');
        $this->addcss('stories.css');
    }

    public function onStory($packet)
    {
        $post = $packet->content;

        if ($post->isStory()) {
            $this->ajaxHttpGet();
        }
    }

    public function onDelete($packet)
    {
        if ($packet->content['server'] == $this->user->id
         && $packet->content['node'] == Post::STORIES_NODE) {
            Toast::send($this->__('stories.deleted'));
            $this->ajaxHttpGet();
        }
    }

    public function ajaxHttpGet()
    {
        $view = $this->tpl();
        $posts = Post::myStories()->withCount('myViews')->get();
        $view->assign('stories', $posts);

        $this->rpc('MovimTpl.fill', '#stories', $view->draw('_stories'));
    }
}
