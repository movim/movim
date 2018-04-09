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

        $blogs = \App\Post::where('node', 'urn:xmpp:microblog:0')
                          ->where('open', true)
                          ->orderBy('published', 'desc')
                          ->get();
        $view->assign('blogs', $blogs);

        return $view->draw('_contactdiscoposts', true);
    }
}
