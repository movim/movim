<?php

class NewsNav extends \Movim\Widget\Base
{
    public function load()
    {

    }

    public function display()
    {
        $nd = new \Modl\PostnDAO;
        $cd = new \Modl\ContactDAO;

        $this->view->assign('presencestxt', getPresencesTxt());
        $this->view->assign('top', $cd->getTop(6));
        $this->view->assign('blogs', $nd->getLastBlogPublic(0, 3));
        $this->view->assign('posts', $nd->getLastPublished(0, 2));
        $this->view->assign('me', $cd->get($this->user->getLogin()), true);
        $this->view->assign('jid', $this->user->getLogin());
    }
}
