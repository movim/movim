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

    public function preparePosts()
    {
        $view = $this->tpl();

        $nd = new \Modl\PostnDAO;
        $blogs = $nd->getLastBlogPublic(0, 36);
        $blogs = is_array($blogs) ? $blogs : [];

        $view->assign('blogs', $blogs);

        return $view->draw('_contactdiscoposts', true);
    }
}
