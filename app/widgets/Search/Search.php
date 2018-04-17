<?php

use Respect\Validation\Validator;

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
                $posts = \App\Post::whereIn('id', function($query) use ($key) {
                    $query->select('post_id')
                          ->from('post_tag')
                          ->whereIn('tag_id', function($query) use ($key) {
                            $query->select('id')
                                  ->from('tags')
                                  ->where('name', 'like', '%' . strtolower($key) . '%');
                          });
                })
                ->whereIn('id', function($query) {
                    $query = $query->select('id')->from('posts');

                    $query = \App\Post::withContactsScope($query);
                    $query = \App\Post::withMineScope($query);
                    $query = \App\Post::withSubscriptionsScope($query);
                })
                ->orderBy('published', 'desc')
                ->take(6)
                ->get();

                $view->assign('posts', $posts);
            }

            if ($posts == false) $view->assign('empty', true);
        }

        if (!empty($key)) {
            $contacts = \App\Contact::whereIn('id', function ($query) use ($key) {
                $query->select('id')
                      ->from('users')
                      ->where('public', true)
                      ->where('id', 'like', '%'. $key . '%');
            })->limit(5)->get();

            if (Validator::email()->validate($key)) {
                $contact = new \App\Contact;
                $contact->jid = $key;
                $contacts->push();
            }

            $view->assign('contacts', $contacts);
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

    public function prepareTicket(\App\Post $post)
    {
        return (new Post)->prepareTicket($post);
    }
}
