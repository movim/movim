<?php

class CommunitiesDiscover extends \Movim\Widget\Base
{
    private $_paging = 20;

    public function load()
    {
        $this->addcss('communitiesdiscover.css');
    }

    public function preparePosts($page = 0)
    {
        $view = $this->tpl();

        $pd = new \Modl\PostnDAO;

        $posts = $pd->getLastPublished(false, $page * $this->_paging, $this->_paging + 1);
        $posts = is_array($posts) ? $posts : [];

        $view->assign('posts', $posts);

        return $view->draw('_communitiesdiscover_posts', true);
    }

    public function display()
    {
        $this->view->assign('posts', $this->preparePosts());
    }
}
