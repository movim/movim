<?php

use App\Configuration;
use Movim\Widget\Base;

class NewsNav extends Base
{
    public function display()
    {
        $nd = new \Modl\PostnDAO;
        $configuration = Configuration::findOrNew(1);

        $blogs = $nd->getLastBlogPublic(
            rand(0, 5),
            5,
            ($configuration->restrictsuggestions) ? $this->user->getServer() : false
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
            ($configuration->restrictsuggestions) ? $this->user->getServer() : false
        );

        $posts = is_array($posts) ? $posts : [];

        shuffle($posts);

        $this->view->assign('posts', $posts);
    }
}
