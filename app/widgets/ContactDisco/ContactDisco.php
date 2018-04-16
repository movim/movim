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

        $users = \App\Contact::whereIn('id', function ($query) {
            $query->select('id')
                  ->from('users')
                  ->where('public', true);
        })->limit(40)->get();

        $view->assign('presencestxt', getPresencesTxt());
        $view->assign('users', $users);

        return $view->draw('_contactdisco', true);
    }
}
