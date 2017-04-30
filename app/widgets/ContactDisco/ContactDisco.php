<?php

class ContactDisco extends \Movim\Widget\Base
{
    public function load()
    {
    }

    public function display()
    {
        $nd = new \Modl\PostnDAO;

        $blogs = $nd->getLastBlogPublic(rand(0, 5), 6);
        $blogs = is_array($blogs) ? $blogs : [];

        shuffle($blogs);

        $cd = new \Modl\ContactDAO;
        $users = $cd->getAllPublic(0, 10);

        $this->view->assign('blogs', $blogs);
        $this->view->assign('users', $users);
    }
}
