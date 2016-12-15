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
        $this->view->assign('blogs', $nd->getLastBlogPublic(0, 4));

        $count = ($this->getView() == 'news') ? 3 : 6;
        $origin = ($this->get('s') && $this->get('s') != 'subscriptions') ?
            $this->get('s') : false;

        $this->view->assign('posts', $nd->getLastPublished($origin, 0, $count));

        $this->view->assign('me', $cd->get($this->user->getLogin()), true);
        $this->view->assign('jid', $this->user->getLogin());
    }
}
