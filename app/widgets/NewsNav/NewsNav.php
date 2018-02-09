<?php

class NewsNav extends \Movim\Widget\Base
{
    public function load()
    {
    }

    public function display()
    {
        $nd = new \Modl\PostnDAO;
        $cd = new \Modl\ConfigDAO;
        $config = $cd->get();

        $blogs = $nd->getLastBlogPublic(
            rand(0, 5),
            5,
            ($config->restrictsuggestions == true) ? $this->user->getServer() : false
        );
        $blogs = is_array($blogs) ? $blogs : [];

        shuffle($blogs);

        $this->view->assign('blogs', $blogs);

        $origin = ($this->get('s') && $this->get('s') != 'subscriptions') ?
            $this->get('s') : false;

        $posts = $nd->getLastPublished(
            $origin,
            0,
            6,
            ($config->restrictsuggestions == true) ? $this->user->getServer() : false
        );

        $posts = is_array($posts) ? $posts : [];

        shuffle($posts);

        $this->view->assign('posts', $posts);
    }
}
