<?php

use Movim\Widget\Base;

use Respect\Validation\Validator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Capsule\Manager as DB;

class Search extends Base
{
    public function load()
    {
        $this->addjs('search.js');
        $this->addcss('search.css');
    }

    public function ajaxRequest()
    {
        $view = $this->tpl();

        $view->assign('empty', $this->prepareSearch(''));
        $view->assign('contacts', $this->user->session->topContacts()
                                             ->with('presence.capability')
                                             ->get());

        Drawer::fill($view->draw('_search'), true);

        $this->rpc('Search.init');
    }

    public function prepareSearch($key)
    {
        $view = $this->tpl();

        $key = str_replace(['#', 'xmpp:'], '', $key);

        if (Validator::stringType()->length(1, 64)->validate($key)) {
            $view->assign('posts', new Collection);

            if ($this->user->hasPubsub()) {
                $posts = \App\Post::whereIn('id', function ($query) use ($key) {
                    $query->select('post_id')
                          ->from('post_tag')
                          ->whereIn('tag_id', function ($query) use ($key) {
                              $query->select('id')
                                  ->from('tags')
                                  ->where('name', 'like', '%' . strtolower($key) . '%');
                          });
                })
                ->whereIn('id', function ($query) {
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

            $contacts = \App\Contact::whereIn('id', function ($query) use ($key) {
                $query->select('id')
                      ->from('users')
                      ->where('public', true)
                      ->where('id', 'like', '%'. $key . '%');
            })->limit(5)->get();

            if (Validator::email()->validate($key)) {
                $contact = new \App\Contact;
                $contact->id = $key;
                $contacts->push($contact);
            }

            $view->assign('contacts', $contacts);

            return $view->draw('_search_results');
        }

        return $this->prepareEmpty();
    }

    public function prepareEmpty()
    {
        $view = $this->tpl();

        $users = \App\Contact::whereIn('id', function ($query) {
            $query->select('id')
                  ->from('users')
                  ->where('public', true);
        })
        ->join(DB::raw('(
            select min(value) as value, jid
            from presences
            group by jid) as presences
            '), 'presences.jid', '=', 'contacts.id')
        ->whereNotIn('id', function ($query) {
            $query->select('jid')
                  ->from('rosters')
                  ->where('session_id', $this->user->session->id);
        })
        ->where('id', '!=', $this->user->id)
        ->orderBy('presences.value')
        ->limit(15)->get();

        $view->assign('presencestxt', getPresencesTxt());
        $view->assign('users', $users);

        return $view->draw('_search_results_empty');
    }

    public function ajaxSearch($key)
    {
        $this->rpc('MovimTpl.fill', '#results', $this->prepareSearch($key));
        $this->rpc('Search.searchClear');
    }

    public function ajaxChat($jid)
    {
        $contact = new ContactActions;
        $contact->ajaxChat($jid);
    }

    public function prepareTicket(\App\Post $post)
    {
        return (new Post)->prepareTicket($post);
    }
}
