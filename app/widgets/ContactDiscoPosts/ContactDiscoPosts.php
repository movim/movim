<?php

class ContactDiscoPosts extends \Movim\Widget\Base
{
    public function load()
    {
        $this->addjs('contactdiscoposts.js');
    }

    public function ajaxGet()
    {
        $this->rpc('MovimTpl.fill', '#contactdiscoposts', $this->preparePosts());
    }

    public function prepareTicket(\App\Post $post)
    {
        return (new Post)->prepareTicket($post);
    }

    public function preparePosts()
    {
        $view = $this->tpl();

        $blogs = \App\Post::restrictToMicroblog()
                          ->restrictUserHost()
                          ->restrictNSFW()
                          ->recents()
                          ->where('open', true)
                          ->orderBy('posts.published', 'desc')
                          ->get();
        $view->assign('blogs', $blogs);

        return $view->draw('_contactdiscoposts', true);
    }
}
