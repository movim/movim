<?php

namespace App\Widgets\Stories;

use App\Post;
use Movim\Widget\Base;

class Stories extends Base
{
    public function load()
    {
        $this->registerEvent('post', 'onStory');

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

    public function ajaxHttpGet()
    {
        $view = $this->tpl();
        $posts = Post::myStories()->get();
        $view->assign('stories', $posts);

        $this->rpc('MovimTpl.fill', '#stories', $view->draw('_stories'));
    }
}
