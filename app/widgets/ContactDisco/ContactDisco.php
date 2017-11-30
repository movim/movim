<?php

class ContactDisco extends \Movim\Widget\Base
{
    public function load()
    {
        $this->addjs('contactdisco.js');
    }

    public function ajaxGet()
    {
        $this->rpc('MovimTpl.fill', '#contactdisco', $this->prepareContacts());
    }

    public function prepareContacts()
    {
        $view = $this->tpl();

        $nd = new \Modl\PostnDAO;
        $blogs = $nd->getLastBlogPublic(0, 6);
        $blogs = is_array($blogs) ? $blogs : [];

        $cd = new \Modl\ContactDAO;
        $users = $cd->getAllPublic(0, 16);

        $view->assign('presencestxt', getPresencesTxt());
        $view->assign('blogs', $blogs);
        $view->assign('users', $users);

        return $view->draw('_contactdisco', true);
    }
}
