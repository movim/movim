<?php

use Respect\Validation\Validator;
use Modl\PostnDAO;

class Search extends \Movim\Widget\Base
{
    function load()
    {
        $this->addjs('search.js');
        $this->addcss('search.css');
    }

    function ajaxRequest()
    {
        $view = $this->tpl();
        $view->assign('empty', $this->prepareSearch(''));
        $view->assign('contacts', App\User::me()->session->contacts);

        Drawer::fill($view->draw('_search', true), true);

        $this->rpc('Search.init');
    }

    function prepareSearch($key)
    {
        $view = $this->tpl();

        $validate_subject = Validator::stringType()->length(1, 15);
        if (!$validate_subject->validate($key)) {
            $view->assign('empty', true);
        } else {
            $view->assign('empty', false);
            $view->assign('presencestxt', getPresencesTxt());

            $posts = false;

            if ($this->user->hasPubsub()) {
                $pd = new PostnDAO;
                $posts = $pd->search($key);
            }

            $view->assign('posts', $posts);

            if (!$posts) $view->assign('empty', true);
        }

        if (!empty($key)) {
            $contacts = App\Contact::where('id', function ($query) {
                $query->select('id')
                      ->from('users')
                      ->where('public', true);
            })->limit(5)->get();

            $view->assign('contacts', $contacts);

            if (Validator::email()->validate($key)) {
                $contact = new App\Contact;
                $contact->jid = $key;
                $view->assign('contacts', [$contact]);
            }
        } else {
            $view->assign('contacts', null);
        }

        return $view->draw('_search_results', true);
    }

    function ajaxSearch($key)
    {
        $this->rpc('MovimTpl.fill', '#results', $this->prepareSearch($key));
        $this->rpc('Search.searchClear');
    }

    function ajaxChat($jid)
    {
        $contact = new ContactActions;
        $contact->ajaxChat($jid);
    }
}
