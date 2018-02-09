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

        $cd = new \Modl\ContactDAO;
        $users = $cd->getAllPublic(0, 40);

        $view->assign('presencestxt', getPresencesTxt());
        $view->assign('users', $users);

        return $view->draw('_contactdisco', true);
    }
}
