<?php

use Movim\User;

include_once WIDGETS_PATH.'Post/Post.php';

class Home extends \Movim\Widget\Base
{
    public $_paging = 8;

    function load()
    {
        $this->addcss('home.css');
    }

    public function preparePost($p)
    {
        $pw = new Post;
        return $pw->preparePost($p, true, true, true);
    }

    function display()
    {
        $pd = new \Modl\PostnDAO;

        $page = $this->get('i') !== null ? $this->get('i') : 0;

        $posts = $pd->getAllPublishedPublic($page * $this->_paging, $this->_paging + 1);

        if(count($posts) == $this->_paging + 1) {
            array_pop($posts);
            $more = $page + 1;
        } else {
            $more = null;
        }

        $this->view->assign('posts', $posts);
        $this->view->assign('more', $more);
    }
}
