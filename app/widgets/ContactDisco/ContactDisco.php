<?php

class ContactDisco extends \Movim\Widget\Base
{
    public function load()
    {
    }

    public function display()
    {
        $nd = new \Modl\PostnDAO;

        $blogs = $nd->getLastBlogPublic(0, 6);
        $blogs = is_array($blogs) ? $blogs : [];

        $cd = new \Modl\ContactDAO;
        $users = $cd->getAllPublic(0, 16);

        $this->view->assign('presencestxt', getPresencesTxt());
        $this->view->assign('blogs', $blogs);
        $this->view->assign('users', $users);
    }
}
